<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminCompany extends Model
{
    use HasUuids, softDeletes;

    protected $table = 'admin_companies';

    protected $fillable = [
        'name',
        'document_cnpj',
        'is_active',
        'max_condominiums'
    ];
}
