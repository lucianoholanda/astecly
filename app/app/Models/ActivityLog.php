<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    // Permite guardar todos os campos em massa
    protected $guarded = [];

    // Converte os campos JSON automaticamente para arrays no PHP
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Relação Polimórfica: Retorna o registo original (OS, Cliente, Peça, etc.)
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Retorna o utilizador (técnico/admin) que fez a ação
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna a assistência técnica (Tenant) dona do registo
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}