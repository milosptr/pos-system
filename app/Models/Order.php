<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['table_id', 'total', 'order'];
    protected $casts = ['order' => 'array'];
    public $timestamps = true;

    /** Relationships **/

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

}
