<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommonArea extends Model
{
    use SoftDeletes, HasUuids;

    protected $table = 'common_areas';

    protected $fillable = [
        'condominium_id',
        'name',
        'capacity',
        'photo_url',
        'booking_rules',
        'is_active',
    ];
}
