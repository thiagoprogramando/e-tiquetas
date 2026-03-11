<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Label extends Model {
    
    protected $table = 'labels_data';

    protected $fillable = [
        'uuid',
        'company_id',
        'layout_id',
        'data_id',
        'created_by',
        'file_url',
        'file_name',
    ];

    public function company() {
        return $this->belongsTo(User::class, 'company_id')->withTrashed();
    }

    public function user() {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function layout() {
        return $this->belongsTo(Layout::class, 'layout_id')->withTrashed();
    }

    public function data() {
        return $this->belongsTo(Data::class, 'data_id')->withTrashed();
    }
    
}
