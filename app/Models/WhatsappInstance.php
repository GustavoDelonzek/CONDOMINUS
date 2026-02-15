<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappInstance extends Model
{
    use HasUuids;

    protected $table = 'whatsapp_instances';

    protected $fillable = [
        'condominium_id',
        'instance_id',
        'instance_token',
        'status',
        'phone_number_connected',
    ];

    public function condominium(): BelongsTo
    {
        return $this->belongsTo(Condominium::class);
    }
}
