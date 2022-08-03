<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $fillable = ['company', 'price', 'date', 'message', 'done', 'type'];
    public $timestamps = true;


}
