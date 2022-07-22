<?php

namespace App\Models;

use App\Models\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'table_id', 'status', 'order', 'total', 'payment_type', 'note'];
    protected $casts = ['order' => 'array'];
    public $timestamps = true;


    // RELATIONSHIPS

    public function table()
    {
      return $this->belongsTo(Table::class);
    }
}
