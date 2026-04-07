<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model implements HasName
{
    use SoftDeletes, LogsActivity; 

    protected $guarded = [];

    public function getFilamentName(): string
    {
        return $this->company?->company_name ?? (string) $this->slug;
    }

    public function getTenantLabel(): string
    {
        return $this->company?->company_name ?? (string) $this->slug;
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}