<?php

namespace App\Models;

use App\Models\Traits\SalesRevenue;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory, SalesRevenue;

    const TYPE_EPOS = 1;
    const TYPE_EBAR = 2;

    const STATUS_ACTIVE = 1;

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
      'type',
      'batch_id',
      'created_at',
    ];

    public $timestamps = true;
}
