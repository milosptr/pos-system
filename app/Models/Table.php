<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'area','table_number', 'position_x', 'position_y', 'size', 'rotate', 'position_x_middle', 'position_y_middle'];
    public $timestamps = true;


    /** Relationships **/

    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    public function getTableLocationAttribute()
    {
      return $this->area ? 'Basta' : 'Sala';
    }
}
