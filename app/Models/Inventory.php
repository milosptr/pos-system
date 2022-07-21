<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';
    protected $fillable = ['category_id', 'name', 'description', 'active', 'sold_by', 'price', 'sku', 'qty', 'color', 'order'];
    protected $casts = ['order' => 'array'];
    public $timestamps = true;
}
