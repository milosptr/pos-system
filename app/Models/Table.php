<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'area', 'position_x', 'position_y'];
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
