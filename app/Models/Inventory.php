<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    CONST SOLD_BY_PIECE = 0;
    CONST SOLD_BY_HALF_PORTION = 1;
    CONST SOLD_BY_GRAMS = 2;

    protected $table = 'inventory';
    protected $fillable = ['category_id', 'name', 'description', 'active', 'sold_by', 'price', 'sku', 'qty', 'color', 'order'];
    protected $casts = ['order' => 'array'];
    public $timestamps = true;

    // RELATIONSHIPS

    public function category()
    {
      return $this->belongsTo(Category::class);
    }
}
