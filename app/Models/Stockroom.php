<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stockroom extends Model
{
    use HasFactory;

    const TYPE_IMPORTED = 1;
    const TYPE_BACKOFFICE = 2;

    protected $fillable = [
        'inventory_id',
        'sku',
        'name',
        'qty',
        'tax',
        'unit',
        'total',
        'type',
        'created_at',
    ];
    public $timestamps = true;
}
