<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageLog extends Model
{
    use SoftDeletes, HasUuids;

    protected $table = 'message_logs';

    protected $fillable = [
        'condominium_id',
        'user_id',
        'phone_number',
        'direction',
        'content',
        'media_url',
    ];
}
