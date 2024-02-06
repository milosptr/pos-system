<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseInventory extends Model
{
    use HasFactory;

    protected $table = 'warehouse_inventory';
    protected $fillable = [
        'warehouse_id',
        'inventory_id',
        'norm'
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
