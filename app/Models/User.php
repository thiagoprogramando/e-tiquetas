<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'uuid',
        'parent_id',
        'avatar',
        'name',
        'cpfcnpj',
        'email',
        'password',
        'endpoint',
        'role'
    ];

    public function company() {
        return $this->belongsTo(User::class, 'parent_id')->withTrashed();
    }

    public function affiliates() {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function relatedUserIds() {
        if ($this->parent_id === null) {
            return $this->affiliates()->pluck('id')->push($this->id);
        }

        return collect([$this->id]);
    }

    public function datas() {
        return Data::whereIn('created_by', $this->relatedUserIds());
    }

    public function layouts() {
        return Layout::whereIn('created_by', $this->relatedUserIds());
    }

    public function allAffiliates() {
        if ($this->parent_id === null) {
            return $this->affiliates();
        }

        return collect();
    }

    public function labels() {
        return Label::whereIn('created_by', $this->relatedUserIds());
    }

    public function maskName() {
        
        $names = explode(' ', trim($this->name));
        return implode(' ', array_slice($names, 0, 2));
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
