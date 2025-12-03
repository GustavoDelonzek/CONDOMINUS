<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Condominium extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'condominiums';

    protected $fillable = [
        'name',
        'admin_company_id',
        'address_full',
        'settings',
    ];

    public function adminCompany(): BelongsTo
    {
        return $this->belongsTo(AdminCompany::class, 'admin_company_id', 'id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'condominium_id', 'id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'condominium_id', 'id');
    }

    public function commonAreas(): HasMany
    {
        return $this->hasMany(CommonArea::class, 'condominium_id', 'id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'condominium_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'memberships',
            'condominium_id',
            'user_id'
        )->withPivot([
            'role',
            'unit_id',
            'is_active',
        ])->withTimestamps();
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'condominium_id', 'id');
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(Occurrence::class, 'condominium_id', 'id');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class, 'condominium_id', 'id');
    }
}
