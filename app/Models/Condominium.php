<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Condominium extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'condominiums';

    protected $fillable = [
        'name',
        'admin_company_id',
        'address_full',
        'settings',
    ];


}
