<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceType extends Model
{
    use LogsActivity;
    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}