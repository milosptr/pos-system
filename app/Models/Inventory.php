<?php

namespace App\Models;

use App\Models\Traits\InventoryFilters;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory, InventoryFilters;

    const SOLD_BY_PIECE = 0;
    const SOLD_BY_HALF_PORTION = 1;
    const SOLD_BY_GRAMS = 2;

    protected $table = 'inventory';
    protected $fillable = ['category_id', 'name', 'description', 'active', 'sold_by', 'price', 'sku', 'qty', 'color', 'order'];
    protected $casts = ['order' => 'array'];
    public $timestamps = true;

    // RELATIONSHIPS

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function pricing()
    {
        return $this->hasMany(InventoryPricing::class);
    }

    public static function skuMask($sku)
    {
        return str_pad($sku, 6, '0', STR_PAD_LEFT);
    }
}
