<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenOrderItem extends Model
{
    protected $fillable = [
        'kitchen_order_id',
        'external_item_id',
        'category_id',
        'sku',
        'name',
        'qty',
        'modifier',
        'storno',
        'is_done',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'storno' => 'boolean',
        'is_done' => 'boolean',
    ];

    public function kitchenOrder()
    {
        return $this->belongsTo(KitchenOrder::class);
    }
}
