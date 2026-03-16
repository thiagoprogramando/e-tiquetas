<?php

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use App\Models\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use GuzzleHttp\Client;
use Maatwebsite\Excel\Facades\Excel;

class DataController extends Controller {
    
    public function index (Request $request) {

        $query = Data::query()->where(function ($q) {
            $q->where('company_id', Auth::user()->company_id)
              ->orWhere('created_by', Auth::id());
        });

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }

        if ($request->filled('trash')) {
            $query->onlyTrashed();
        }

        return view('app.Data.index', [
            'data' => $query->latest()->paginate(30)
        ]);
    }

    public function create ($data = null) {

        return view('app.Data.create', [
            'data' => $data
        ]);
    }

    public function process(Request $request) {

        switch ($request->method) {
            case 'excel':
                return $this->importExcel($request);
                break;
            case 'api':
                return $this->importApi($request);
                break;
            case 'document':
                return redirect()->back()->with('info', 'Importação via documento não é suportada!');
                break;
        }

        return redirect()->back()->with('error', 'Método de importação não reconhecido!');
    }

    private function importExcel (Request $request) {
       
        try {

            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
            ]);

            $file       = $request->file('file');
            $method     = $request->input('method', 'file');
            $collection = Excel::toCollection(null, $file);
            $rows       = $collection->first();

            if (!$rows || $rows->count() < 2) {
                return back()->withErrors([
                    'file' => 'O arquivo precisa conter pelo menos 1 linha de cabeçalho e 1 linha de dados.'
                ]);
            }

            $headers = $rows->first()->map(fn ($item) => trim((string) $item))->toArray();
            $headers = array_values(array_filter($headers));
            if (empty($headers)) {
                return back()->withErrors([
                    'file' => 'Não foi possível identificar os cabeçalhos do arquivo.'
                ]);
            }

            $dataRows = $rows->slice(1)->map(function ($row) use ($headers) {

                $rowArray = $row->toArray();
                $mapped = [];

                foreach ($headers as $index => $header) {
                    $mapped[$header] = $rowArray[$index] ?? null;
                }

                if (empty(array_filter($mapped))) {
                    return null;
                }

                return $mapped;

            })->filter()->values()->toArray();

            if (empty($dataRows)) {
                return back()->withErrors([
                    'file' => 'O arquivo não possui linhas de dados válidas.'
                ]);
            }

            $data = [
                'name'      => $file->getClientOriginalName(),
                'method'    => $method,
                'headers'   => $headers,
                'rows'      => $dataRows,
                'preview'   => array_slice($dataRows, 0, 50),
                'total'     => count($dataRows),
            ];

            return view('app.Data.create', [
                'data' => $data
            ]);
        } catch (\Throwable $e) {

            $errorId = (string) Str::uuid();

            Log::error('Erro ao processar importação de arquivo', [
                'error_id' => $errorId,
                'user_id'  => Auth::user()->id ?? null,
                'message'  => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
                'file'     => $request->file('file')?->getClientOriginalName(),
            ]);

            return back()->withErrors([
                'file' => "Erro ao processar o arquivo. Código do erro: {$errorId}"
            ]);
        }
    }

    private function importApi(Request $request) {
        
        try {

            $client = new Client();

            $headers = [];
            if ($request->has('custom_headers')) {
                $keys   = $request->custom_headers['key'] ?? [];
                $values = $request->custom_headers['value'] ?? [];

                foreach ($keys as $index => $key) {
                    if (!empty($key) && isset($values[$index])) {
                        $headers[$key] = $values[$index];
                    }
                }
            }

            if ($request->auth_type === 'bearer' && !empty($request->auth['token'])) {
                $headers['Authorization'] = 'Bearer ' . $request->auth['token'];
            }
            if ($request->auth_type === 'apikey' && !empty($request->auth['key_name'])) {
                $headers[$request->auth['key_name']] = $request->auth['key_value'] ?? '';
            }

            $params = [];
            if ($request->has('params')) {
                $keys   = $request->params['key'] ?? [];
                $values = $request->params['value'] ?? [];

                foreach ($keys as $index => $key) {
                    if (!empty($key) && isset($values[$index])) {
                        $params[$key] = $values[$index];
                    }
                }
            }

            $response = $client->request($request->request_method, $request->url, [
                'headers' => $headers,
                'query'   => $params,
                'timeout' => 30,
                'verify'  => false,
            ]);

            $body = json_decode($response->getBody(), true);
            if (!$body) {
                return back()->with('error', 'Resposta da API inválida ou vazia.');
            }

            $dataPath = $request->data_path ?? null;
            if ($dataPath) {
                $data = data_get($body, $dataPath);
            } else {
                $data = null;
                foreach ($body as $key => $value) {
                    if (is_array($value) && isset($value[0]) && is_array($value[0])) {
                        $data = $value;
                        break;
                    }
                }

                if (!$data) {
                    $data = $body;
                }
            }
            if (!is_array($data)) {
                return back()->with('error', 'Nenhum array de dados encontrado na API.');
            }
            if (!isset($data[0])) {
                $data = [$data];
            }
            $data = array_map(function ($row) {
                return is_array($row) ? $this->flattenArray($row) : $row;
            }, $data);

            $headers = [];
            foreach ($data as $row) {
                $headers = array_unique(array_merge($headers, array_keys($row)));
            }

            $result = [
                'name'    => 'API - ' . parse_url($request->url, PHP_URL_HOST),
                'method'  => 'api',
                'headers' => $headers,
                'rows'    => $data,
                'preview' => array_slice($data, 0, 50),
                'total'   => count($data),
            ];

            return view('app.Data.create', [
                'data' => $result
            ]);
        } catch (\Exception $e) {

            $errorId = (string) Str::uuid();

            Log::error('Erro ao consumir API', [
                'error_id' => $errorId,
                'user_id'  => Auth::user()->id ?? null,
                'message'  => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Erro ao consumir API: ' . $e->getMessage());
        }
    }

    private function flattenArray(array $array, $prefix = '') {
        
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix ? $prefix . '.' . $key : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    public function store(Request $request) {
        try {

            $request->validate([
                'rows' => 'required',
            ]);

            $rows = $request->input('rows');
            if (is_string($rows)) {
                $rows = json_decode($rows, true);
            }
            if (!is_array($rows) || empty($rows)) {
                return back()->withErrors([
                    'rows' => 'Formato de dados inválido.'
                ]);
            }

            $fileName = $request->input('name', 'importação'.now());
            $method   = $request->input('method', 'file');

            $processedRows = [];
            $success = 0;
            $errors  = 0;

            foreach ($rows as $index => $row) {

                if (is_string($row)) {
                    $row = json_decode($row, true);
                }
                if (!is_array($row)) {
                    continue;
                }

                $rowErrors  = [];
                $firstField = array_key_first($row);

                if (empty($row[$firstField] ?? null)) {
                    $rowErrors[] = 'Primeiro campo obrigatório não informado';
                }

                if ($rowErrors) {
                    $status = 'error';
                    $errors++;
                } else {
                    $status = 'success';
                    $success++;
                }

                $processedRows[] = [
                    'index'  => $index + 1,
                    'status' => $status,
                    'errors' => $rowErrors ?: null,
                    'data'   => $row
                ];
            }

            $payload = [
                'meta' => [
                    'file_name'     => $fileName,
                    'total_rows'    => count($rows),
                    'imported_rows' => $success,
                    'failed_rows'   => $errors,
                    'created_at'    => now()->toDateTimeString(),
                ],
                'rows' => $processedRows,
                'stats' => [
                    'success' => $success,
                    'errors'  => $errors,
                ],
            ];

            Data::create([
                'company_id' => Auth::user()->company_id ?? Auth::user()->id,
                'created_by' => Auth::user()->id,
                'name'       => $request->name ?? 'Arquivo de importação ' . now()->format('Y-m-d H:i:s'),
                'method'     => $method,
                'status'     => 'full',
                'message'    => 'Importação processada com sucesso',
                'config'     => null,
                'data'       => $payload,
            ]);

            return redirect()->route('data')->with('success', 'Importação realizada com sucesso!');

        } catch (\Throwable $e) {

            $errorId = (string) Str::uuid();

            Log::error('Erro ao confirmar importação', [
                'error_id' => $errorId,
                'message'  => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return redirect()->route('create-data')->with(
                'error',
                "Erro ao confirmar importação. Código: {$errorId}"
            );
        }
    }

    public function destroy (Request $request) {

        $data = Data::where('uuid', $request->uuid)->first();
        if ($data && $data->delete()) {
            return redirect()->back()->with('success', 'Base de Dados excluída com sucesso!');
        }

        return redirect()->back()->with('infor', 'Base de Dados não encontrada/disponível, verifique os dados e tente novamente!');
    }
}
