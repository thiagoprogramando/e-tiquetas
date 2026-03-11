<?php

namespace App\Http\Controllers\Label;

use App\Http\Controllers\Controller;
use App\Models\Data;
use App\Models\Label;
use App\Models\Layout;
use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ImageCacheService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use TCPDF;


class LabelController extends Controller {

    private $imageCache;

    public function __construct(ImageCacheService $imageCache) {
        $this->imageCache = $imageCache;
    }

    public function render(Request $request) {

        $layout     = $this->getLayout($request->layout);
        $rows       = $this->getDataRows($request->data);
        $pdf        = $this->createPdf($layout);
        $elements   = $this->getCanvasElements($layout);

        $this->renderPages($pdf, $layout, $rows['rows'], $elements);

        /*
        |--------------------------------------------------------------------------
        | Gerar PDF em memória
        |--------------------------------------------------------------------------
        */
        $pdfContent = $pdf->Output('', 'S');

        /*
        |--------------------------------------------------------------------------
        | Nome do arquivo
        |--------------------------------------------------------------------------
        */
        $fileName   = 'label_' . Str::uuid() . '.pdf';
        $path       = 'labels/layout_'.$layout->uuid.'/'.$fileName;

        /*
        |--------------------------------------------------------------------------
        | Salvar no storage
        |--------------------------------------------------------------------------
        */
        Storage::disk('public')->put($path, $pdfContent);

        /*
        |--------------------------------------------------------------------------
        | Criar registro no banco
        |--------------------------------------------------------------------------
        */
        $label              = new Label();
        $label->uuid        = Str::uuid();
        $label->company_id  = Auth::user()->company_id ?? Auth::user()->id;
        $label->layout_id   = $layout->id;
        $label->data_id     = $rows['id'];
        $label->created_by  = Auth::user()->id;
        $label->file_url    = asset('storage/' . $path);
        $label->file_name   = $fileName;
        if ($label->save()) {
            return response($pdfContent)->header('Content-Type', 'application/pdf');
        }

        // return redirect()->route('exports')->with('error', 'Falha ao Gerar Remessa, verifique os dados e tente novamente!');
    }

    private function getLayout($uuid) {

        $layout = Layout::where('uuid', $uuid)->first();
        if (!$layout) {
            abort(404, 'Layout não encontrado');
        }

        return $layout;
    }

    private function getDataRows($uuid) {

        $data = Data::where('uuid', $uuid)->first();
        if (!$data) {
            abort(404, 'Dados não encontrados');
        }

        $payload = is_string($data->data) ? json_decode($data->data, true) : $data->data;
        if (!is_array($payload)) {
            abort(400, 'Estrutura de dados inválida');
        }

        $rows = collect($payload['rows'] ?? [])->where('status', 'success')->pluck('data')->values()->toArray();
        if (empty($rows)) {
            abort(400, 'Nenhum dado válido');
        }

        return [
            'id'   => $data->id,
            'rows' => $rows
        ];
    }

    private function createPdf($layout) {

        $pdf = new TCPDF('P', 'mm', [$layout->paper_width_mm, $layout->paper_height_mm], true, 'UTF-8', false);
        $pdf->SetMargins(0,0,0);
        $pdf->SetAutoPageBreak(false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        return $pdf;
    }

    private function getCanvasElements($layout) {

        $canvas = $layout->canvas_json;
        if (is_string($canvas)) {
            $canvas = json_decode($canvas, true);
        }

        if (!is_array($canvas)) {
            return [];
        }

        return $canvas['objects'] ?? $canvas;
    }

    private function renderPages($pdf, $layout, $rows, $elements) {

        $columns    = $layout->columns;
        $rowsLayout = $layout->rows;
        $perPage    = $columns * $rowsLayout;
        $pages      = ceil(count($rows) / $perPage);

        $index = 0;
        for ($p = 0; $p < $pages; $p++) {

            $pdf->AddPage();

            for ($r = 0; $r < $rowsLayout; $r++) {
                for ($c = 0; $c < $columns; $c++) {

                    if (!isset($rows[$index])) {
                        return;
                    }

                    $this->renderLabel($pdf, $layout, $rows[$index], $elements, $r, $c);
                    $index++;
                }
            }
        }
    }

    private function renderLabel($pdf, $layout, $rowData, $elements, $row, $col) {

        $posX = $layout->margin_left_mm + ($col * ($layout->label_width_mm + $layout->gap_x_mm));
        $posY = $layout->margin_top_mm  + ($row * ($layout->label_height_mm + $layout->gap_y_mm));

        $pdf->Rect($posX, $posY, $layout->label_width_mm, $layout->label_height_mm);

        foreach ($elements as $obj) {

            $type = $obj['type'] ?? null;

            switch ($type) {

                case 'text':
                    $this->renderText($pdf, $obj, $rowData, $posX, $posY);
                    break;

                case 'image':
                    $this->renderImage($pdf, $obj, $rowData, $posX, $posY);
                    break;

                case 'barcode':
                    $this->renderBarcode($pdf, $obj, $rowData, $posX, $posY);
                    break;

                case 'qrcode':
                    $this->renderQrCode($pdf, $obj, $rowData, $posX, $posY);
                    break;
            }
        }
    }

    private function renderText($pdf, $obj, $rowData, $posX, $posY) {

        $left   = $obj['position']['x_mm'] ?? (($obj['left'] ?? 0) / 3.78);
        $top    = $obj['position']['y_mm'] ?? (($obj['top'] ?? 0) / 3.78);
        $x      = $posX + $left;
        $y      = $posY + $top;
        $width  = $obj['size']['width_mm'] ?? 20;
        $height = $obj['size']['height_mm'] ?? 6;
        $text   = '';

        if (($obj['content']['mode'] ?? null) === 'dynamic') {
            $field = $obj['content']['value'] ?? null;
            $text = $rowData[$field] ?? '';
        } else {
            $text = $obj['text'] ?? ($obj['content']['value'] ?? '');
        }

        foreach ($rowData as $key => $value) {
            $text = str_replace('{{'.$key.'}}', $value, $text);
        }

        $fontSize = $obj['style']['font_size'] ?? 10;
        $align = strtoupper(substr($obj['style']['text_align'] ?? 'left', 0, 1));

        $pdf->SetFont('Times', '', $fontSize * 0.27);
        $pdf->SetXY($x, $y);

        $pdf->MultiCell($width, $height, $text, 0, $align);
    }

    private function renderImage($pdf, $obj, $rowData, $posX, $posY) {

        $left       = $obj['position']['x_mm'] ?? (($obj['left'] ?? 0) / 3.78);
        $top        = $obj['position']['y_mm'] ?? (($obj['top'] ?? 0) / 3.78);
        $x          = $posX + $left;
        $y          = $posY + $top;
        $width      = $obj['size']['width_mm'] ?? 20;
        $height     = $obj['size']['height_mm'] ?? 6;
        $imageUrl   = null;

        if (($obj['content']['mode'] ?? null) === 'dynamic') {
            $field = $obj['content']['value'] ?? null;
            $imageUrl = $rowData[$field] ?? null;
        } else {
            $imageUrl = $obj['content']['value'] ?? null;
        }

        if (!$imageUrl) {
            return;
        }

        $imagePath = $this->imageCache->get($imageUrl);
        if (!$imagePath) {
            return;
        }

        try {
            $pdf->Image($imagePath, $x, $y, $width, $height);
        } catch (\Exception $e) {
            
        }
    }

    private function renderBarcode($pdf, $obj, $rowData, $posX, $posY) {

        $left   = $obj['position']['x_mm'] ?? (($obj['left'] ?? 0) / 3.78);
        $top    = $obj['position']['y_mm'] ?? (($obj['top'] ?? 0) / 3.78);
        $x      = $posX + $left;
        $y      = $posY + $top;
        $width  = $obj['size']['width_mm'] ?? 30;
        $height = $obj['size']['height_mm'] ?? 10;

        if (($obj['content']['mode'] ?? null) === 'dynamic') {
            $field = $obj['content']['value'] ?? null;
            $code = $rowData[$field] ?? '';
        } else {
            $code = $obj['content']['value'] ?? '';
        }

        if (!$code) return;

        $barcodeType = $obj['barcode']['type'] ?? 'C128';

        if ($barcodeType == 'EAN13') {
            $code = preg_replace('/\D/', '', $code);
            $code = str_pad(substr($code, 0, 13), 13, '0', STR_PAD_LEFT);
        }

        $alignMap = [
            'left' => 'L',
            'center' => 'C',
            'right' => 'R'
        ];

        $align = $alignMap[$obj['style']['text_align'] ?? 'left'];

        $style = [
            'align' => $align,
            'stretch' => false,
            'fitwidth' => true,
            'border' => false,
            'padding' => 0,
            'fgcolor' => [0,0,0],
            'bgcolor' => false,
            'text' => $obj['barcode']['show_text'] ?? false,
            'font' => 'Times',
            'fontsize' => $obj['style']['font_size'] ?? 5
        ];

        try {
            $pdf->write1DBarcode($code, $barcodeType, $x, $y, $width, $height, 0.4, $style, 'N');
        } catch (\Exception $e) {
        }
    }

    private function renderQrCode($pdf, $obj, $rowData, $posX, $posY) {

        $left   = $obj['position']['x_mm'] ?? (($obj['left'] ?? 0) / 3.78);
        $top    = $obj['position']['y_mm'] ?? (($obj['top'] ?? 0) / 3.78);
        $x      = $posX + $left;
        $y      = $posY + $top;
        $width  = $obj['size']['width_mm'] ?? 20;
        $height = $obj['size']['height_mm'] ?? 20;

        if (($obj['content']['mode'] ?? null) === 'dynamic') {
            $field = $obj['content']['value'] ?? null;
            $value = $rowData[$field] ?? '';
        } else {
            $value = $obj['content']['value'] ?? '';
        }

        if (!$value) return;

        $level = $obj['qrcode']['error_correction'] ?? 'M';

        $style = [
            'border' => false,
            'padding' => 0,
            'fgcolor' => [0,0,0],
            'bgcolor' => false
        ];

        try {
            $pdf->write2DBarcode(
                $value, 'QRCODE,' . $level, $x, $y, $width, $height, $style, 'N'
            );
        } catch (\Exception $e) {

        }
    }
}
