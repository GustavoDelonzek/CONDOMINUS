<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OccurrenceMedia extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'occurrence_media';

    protected $fillable = [
        'occurrence_id',
        'media_url',
        'media_type',
        'uploaded_at',
    ];

    public function occurrence(): BelongsTo
    {
        return $this->belongsTo(Occurrence::class, 'occurrence_id', 'id');
    }
}
