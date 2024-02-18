<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Category;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',
        'category_id',
        'order'
    ];

    public $timestamps = true;

    public function category()
    {
        return $this->belongsTo(WarehouseCategory::class, 'category_id');
    }

}
