<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenOrder extends Model
{
    protected $fillable = [
        'orderable_type',
        'orderable_id',
        'table_name',
        'waiter_name',
        'ready_at',
        'invoiced_at',
    ];

    protected $casts = [
        'ready_at' => 'datetime',
        'invoiced_at' => 'datetime',
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

    /**
     * The bill for this order has already been cashed out. Such an order is
     * deleted the moment the kitchen marks it ready instead of moving to
     * "Izdate", because nothing would ever clear it from there.
     */
    public function isInvoiced(): bool
    {
        return $this->invoiced_at !== null;
    }
}
