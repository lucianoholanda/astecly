<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    use SoftDeletes, LogsActivity;
    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}