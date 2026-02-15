<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenOrder extends Model
{
    protected $fillable = [
        'orderable_type',
        'orderable_id',
        'table_name',
        'ready_at',
    ];

    protected $casts = [
        'ready_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(KitchenOrderItem::class);
    }

    /**
     * Scope to get active (pending) kitchen orders.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('ready_at');
    }

    /**
     * Scope to get ready (completed) kitchen orders.
     */
    public function scopeReady($query)
    {
        return $query->whereNotNull('ready_at');
    }
}
