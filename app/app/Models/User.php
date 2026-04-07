<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements HasTenants
{
    use Notifiable, SoftDeletes, LogsActivity;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Relacionamento com a Assistência (Tenant)
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // OBRIGATÓRIO: Diz ao Filament quais empresas este usuário pode acessar
    public function getTenants(Panel $panel): array|Collection
    {
        return $this->tenant ? [$this->tenant] : [];
    }

    // OBRIGATÓRIO: Valida se o usuário tem permissão para entrar no slug da URL
    public function canAccessTenant(Model $tenant): bool
    {
        return $this->tenant_id === $tenant->id;
    }
}