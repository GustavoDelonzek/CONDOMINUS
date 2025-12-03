<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OccurrenceMedia extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'occurrence_medias';

    protected $fillable = [
        'occurrence_id',
        'media_url',
        'media_type',
        'uploaded_at',
    ];
}
