<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasUuids, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'phone_number',
        'chat_lid',
        'photo_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Pega o identificador que será guardado no token (geralmente o ID do user)
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Adiciona dados extras no token se precisar (aqui deixamos vazio)
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'user_id', 'id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'user_id', 'id');
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(Occurrence::class, 'user_id', 'id');
    }

    public function condominiums(): BelongstoMany
    {
        return $this->belongsToMany(
            Condominium::class,
            'memberships',
            'user_id',
            'condominium_id'
        )->withPivot([
            'role',
            'unit_id',
            'is_active'
        ])->withTimestamps();
    }

    public function hasRoleInCondominium(string $role, string $condominiumId): bool
    {
        return $this->memberships()
            ->where('condominium_id', $condominiumId)
            ->where('role', $role)
            ->where('is_active', true)
            ->exists();
    }
}
