<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderPart extends Model
{
    protected $guarded = [];

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}