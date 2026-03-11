<?php

namespace App\Http\Controllers\Layout;

use App\Http\Controllers\Controller;
use App\Models\Layout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LayoutController extends Controller {
    
    public function index (Request $request) {

        $user    = Auth::user();
        $query   = Layout::query()
                    ->where(function ($q) use ($user) {
                        $q->where('created_by', $user->id)
                        ->orWhere('company_id', $user->parent_id ?? $user->id);
                    });

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('app.Layout.index', [
            'layouts' => $query->latest()->paginate(10)
        ]);
    }

    public function show ($uuid) {

        $layout = Layout::where('uuid', $uuid)->first();
        if (!$layout) {
            return redirect()->back()->with('infor', 'Falha ao abrir Template, tente novamente!');
        }

        return view('app.Layout.show', [
            'layout'            => $layout,
            'initialElements'   => $layout->canvas_json ?? [],
            'editorConfig'      => [
                                    'paper_width_mm'  => $layout->paper_width_mm,
                                    'paper_height_mm' => $layout->paper_height_mm,
                                    'label_width_mm'  => $layout->label_width_mm,
                                    'label_height_mm' => $layout->label_height_mm,
                                    'columns'         => $layout->columns,
                                    'rows'            => $layout->rows,
                                    'margin_top_mm'   => $layout->margin_top_mm,
                                    'margin_left_mm'  => $layout->margin_left_mm,
                                    'gap_x_mm'        => $layout->gap_x_mm,
                                    'gap_y_mm'        => $layout->gap_y_mm,
                                ],
            
        ]);
    }

    public function store (Request $request) {

        $validated = $request->validate(
            [
                'name'              => ['required', 'string', 'max:255'],
                'description'       => ['nullable', 'string', 'max:1000'],
                'paper_format'      => ['required', 'in:A4,LETTER,ROLL,CUSTOM'],

                'paper_width_mm'    => ['nullable', 'numeric', 'min:0'],
                'paper_height_mm'   => ['nullable', 'numeric', 'min:0'],

                'columns'           => ['required', 'integer', 'min:1'],
                'rows'              => ['required', 'integer', 'min:1'],

                'margin_top_mm'     => ['nullable', 'numeric', 'min:0'],
                'margin_left_mm'    => ['nullable', 'numeric', 'min:0'],
                'gap_x_mm'          => ['nullable', 'numeric', 'min:0'],
                'gap_y_mm'          => ['nullable', 'numeric', 'min:0'],
            ],
            [
                'name.required'             => 'Informe um nome para o layout.',
                'name.max'                  => 'O nome do layout pode ter no máximo :max caracteres.',
                'paper_format.required'     => 'Selecione um formato de papel.',
                'paper_format.in'           => 'O formato de papel selecionado é inválido.',
                'paper_width_mm.numeric'    => 'A largura do papel deve ser um número válido.',
                'paper_width_mm.min'        => 'A largura do papel não pode ser negativa.',
                'paper_height_mm.numeric'   => 'A altura do papel deve ser um número válido.',
                'paper_height_mm.min'       => 'A altura do papel não pode ser negativa.',
                'columns.required'          => 'Informe a quantidade de colunas.',
                'columns.integer'           => 'O número de colunas deve ser um número inteiro.',
                'columns.min'               => 'Deve existir pelo menos 1 coluna.',
                'rows.required'             => 'Informe a quantidade de linhas.',
                'rows.integer'              => 'O número de linhas deve ser um número inteiro.',
                'rows.min'                  => 'Deve existir pelo menos 1 linha.',
                'margin_top_mm.numeric'     => 'A margem superior deve ser um número válido.',
                'margin_top_mm.min'         => 'A margem superior não pode ser negativa.',
                'margin_left_mm.numeric'    => 'A margem esquerda deve ser um número válido.',
                'margin_left_mm.min'        => 'A margem esquerda não pode ser negativa.',
                'gap_x_mm.numeric'          => 'O espaço horizontal deve ser um número válido.',
                'gap_x_mm.min'              => 'O espaço horizontal não pode ser negativo.',
                'gap_y_mm.numeric'          => 'O espaço vertical deve ser um número válido.',
                'gap_y_mm.min'              => 'O espaço vertical não pode ser negativo.',
            ]
        );

        $preset = $this->getPaperPreset($request->paper_format);

        $layout                     = new Layout();
        $layout->uuid               = Str::uuid();
        $layout->company_id         = Auth::user()->parent_id ?? Auth::user()->id;
        $layout->created_by         = Auth::user()->id;
        $layout->name               = $request->name;
        $layout->description        = $request->description;
        $layout->paper_format       = $request->paper_format;
        $layout->paper_width_mm     = data_get($preset, 'paper_width_mm', $request->paper_width_mm);
        $layout->paper_height_mm    = data_get($preset, 'paper_height_mm', $request->paper_height_mm);
        $layout->label_width_mm     = data_get($preset, 'label_width_mm', $request->label_width_mm);
        $layout->label_height_mm    = data_get($preset, 'label_height_mm', $request->label_height_mm);
        $layout->columns            = $request->columns;
        $layout->rows               = $request->rows;
        $layout->margin_top_mm      = data_get($preset, 'margin_top_mm', $request->margin_top_mm);
        $layout->margin_left_mm     = data_get($preset, 'margin_left_mm', $request->margin_left_mm);
        $layout->gap_x_mm           = data_get($preset, 'gap_x_mm', $request->gap_x_mm);
        $layout->gap_y_mm           = data_get($preset, 'gap_y_mm', $request->gap_y_mm);
        if ($layout->save()) {
            return redirect()->back()->with('success', 'Template constrúdio com sucesso!');
        }

        return redirect()->back()->with('infor', 'Falha ao construir Template, verifique os dados e tente novamente!');
    }

    public function update (Request $request) {

        $validator = Validator::make($request->all(), [
            'uuid'     => 'required|uuid|exists:label_layouts,uuid',
            'config'   => 'required|array',
            'elements' => 'required|array'
        ], [
            'uuid.required'     => 'Template não encontrado/indisponível!',
            'uuid.exists'       => 'Template não encontrado/indisponível!',
            'config.required'   => 'Configuração do papel é obrigatória!',
            'elements.required' => 'Atribua pelo menos um elemento ao Template!'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {

            $layout = Layout::where('uuid', $request->uuid)->firstOrFail();
            $layout->canvas_json = $request->elements;
            $layout->save();
            
            return response()->json([
                'message' => 'Template salvo com sucesso!',
                'uuid'    => $layout->uuid
            ], 200);
            
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Falha ao tentar salvar o Template. Log: '.$e->getMessage()
            ], 500);
        }
    }

    public function destroy (Request $request) {

        $layout = Layout::where('uuid', $request->uuid)->first();
        if ($layout && $layout->delete()) {
            return redirect()->back()->with('success', 'Template excluído com sucesso!');
        }

        return redirect()->back()->with('infor', 'Template não encontrado/disponível, verifique os dados e tente novamente!');
    }

    private function getPaperPreset(string $format): ?array {
        return [
            'A4' => [
                'paper_width_mm'  => 210,
                'paper_height_mm' => 297,
            ],
            'LETTER' => [
                'paper_width_mm'  => 216,
                'paper_height_mm' => 279,
            ],
            'ROLL' => [
                'paper_width_mm'  => 100,
                'paper_height_mm' => null,
            ],
        ][$format] ?? null;
    }
}
