<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceModality extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'price',
        'requires_scheduling'
    ];

    protected $casts = [
        'requires_scheduling' => 'boolean',
        'price' => 'decimal:2',
    ];
}