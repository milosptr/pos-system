<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryPricing extends Model
{
    use HasFactory;

    protected $table = 'inventory_pricing';
    protected $fillable = ['inventory_id', 'purchase_price', 'retail_price', 'norm', 'date'];
    public $timestamp = true;

    // RELATIONSHIPS
    public function inventory()
    {
      return $this->belongsTo(Inventory::class);
    }
}
