<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    use SoftDeletes, LogsActivity;
    protected $guarded = [];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function serviceOrder(): BelongsTo { return $this->belongsTo(ServiceOrder::class); }
}