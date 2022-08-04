<?php

namespace App\Models;

use App\Models\Traits\Revenue;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory, Revenue;

    protected $fillable = [
      'invoice_id',
      'table_id',
      'inventory_id',
      'category_id',
      'name',
      'category_name',
      'price',
      'qty',
      'sku',
      'status',
      'total',
    ];

    public $timestamps = true;
}
