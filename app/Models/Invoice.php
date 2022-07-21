<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'status', 'order', 'total', 'payment_type', 'note'];
    protected $casts = ['order' => 'array'];
    public $timestamps = true;
}
