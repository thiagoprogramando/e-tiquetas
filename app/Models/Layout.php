<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Layout extends Model {
    
    use SoftDeletes;

    protected $table = 'label_layouts';

    protected $fillable = [
        'uuid',
        'company_id',
        'created_by',
        'name',
        'description',
        'paper_format',
        'paper_width_mm',
        'paper_height_mm',
        'label_width_mm',
        'label_height_mm',
        'rows',
        'columns',
        'margin_top_mm',
        'margin_left_mm',
        'gap_x_mm',
        'gap_y_mm',
        'canvas_json',
        'status'
    ];

    public function labelFormat () {

        $formats = [
            'LETTER' => 'Carta',
            'A4'     => 'A4',
            'ROLL'   => 'Rolo (Térmica)',
            'CUSTOM' => 'Personalizado',
        ];

        return $formats[$this->paper_format] ?? 'INDISPONÍVEL';
    }

    protected $casts = [
        'paper_width_mm'    => 'float',
        'paper_height_mm'   => 'float',
        'margin_top_mm'     => 'float',
        'margin_left_mm'    => 'float',
        'gap_x_mm'          => 'float',
        'gap_y_mm'          => 'float',
        'canvas_json'       => 'array',
    ];
}