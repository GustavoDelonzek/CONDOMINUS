<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'units';

    protected $fillable = [
        'condominium_id',
        'block_id',
        'number',
        'floor',
    ];
}
