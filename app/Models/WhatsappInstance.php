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
        'admin_company_id',
        'instance_id',
        'instance_token',
        'status',
        'phone_number_connected',
    ];

    public function adminCompany(): BelongsTo
    {
        return $this->belongsTo(AdminCompany::class, 'admin_company_id', 'id');
    }
}
