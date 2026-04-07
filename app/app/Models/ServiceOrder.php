<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrder extends Model
{
    use SoftDeletes, LogsActivity;
    protected $guarded = [];

    protected $casts = [
        'photos' => 'array',
        'entry_date' => 'datetime',
        'exit_date' => 'datetime',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function device(): BelongsTo { return $this->belongsTo(Device::class); }
    public function status(): BelongsTo { return $this->belongsTo(ServiceOrderStatus::class, 'service_order_status_id'); }
    
    public function parts(): HasMany
    {
        return $this->hasMany(ServiceOrderPart::class);
    }
}