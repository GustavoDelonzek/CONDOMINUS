<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Occurrence extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'occurrences';

    protected $fillable = [
        'condominium_id',
        'user_id',
        'unit_id',
        'category',
        'description',
        'status',
        'admin_response',
        'responded_at'
    ];
}
