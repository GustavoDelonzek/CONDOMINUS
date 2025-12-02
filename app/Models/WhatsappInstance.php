<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

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
}
