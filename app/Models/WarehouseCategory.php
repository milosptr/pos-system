<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
      'group_id',
      'name',
      'group',
      'order',
      'is_deleted'
    ];
    public $timestamps = true;
}
