<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'name', 'description', 'active', 'sold_by', 'price', 'sku', 'qty', 'color', 'order'];
    public $timestamps = true;
}
