<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminCompany extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'admin_companies';

    protected $fillable = [
        'name',
        'document_cnpj',
        'is_active',
        'max_condominiums'
    ];

    public function condominiums(): HasMany
    {
        return $this->hasMany(Condominium::class, 'admin_company_id', 'id');
    }

    public function whatsappInstance(): HasOne
    {
        return $this->hasOne(WhatsappInstance::class, 'admin_company_id', 'id');
    }
}
