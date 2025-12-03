<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'reservations';

    protected $fillable = [
        'condominium_id',
        'user_id',
        'unit_id',
        'common_area_id',
        'start_time',
        'end_time',
        'status',
        'notes',
    ];
}
