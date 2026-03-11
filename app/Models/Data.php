<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Data extends Model {
    
    use SoftDeletes;

    protected $table = 'data';

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'method',
        'status',
        'message',
        'config',
        'data'
    ];

    public function company() {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function labelIcon () {

        switch ($this->method) {
            case 'file':
                $icon = '<i class="ri-file-excel-2-line"></i>';
                break;
            case 'api':
                $icon = '<i class="ri-webhook-line"></i>';
                break;
            case 'document':
                $icon = '<i class="ri-file-pdf-2-line"></i>';
                break;
            default:
                $icon = '<i class="ri-database-2-line"></i>';
                break;
        }

        return $icon;
    }

    public function labelMethod () {

        switch ($this->method) {
            case 'file':
                $method = 'Excel';
                break;
            case 'api':
                $method = 'API';
                break;
            case 'document':
                $method = 'PDF/Documentos';
                break;
            default:
                $method = '---';
                break;
        }

        return $method;
    }

    public function labelStatus () {

        switch ($this->status) {
            case 'processing':
                $status = '<span class="badge rounded-pill bg-label-warning me-1">Processando</span>';
                break;
            case 'failed':
                $status = '<span class="badge rounded-pill bg-label-danger me-1">Falha</span>';
                break;
            case 'full':
                $status = '<span class="badge rounded-pill bg-label-success me-1">Completo</span>';
                break;
            default:
                $status = '---';
                break;
        }

        return $status;
    }

    protected $casts = [
        'data'   => 'array',
        'config' => 'array',
    ];

    protected static function booted() {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }
}
