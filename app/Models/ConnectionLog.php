<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConnectionLog extends Model
{
    use HasFactory;

    protected $fillable = ['platform', 'start_at', 'end_at'];
    public $timestamps = true;
}
