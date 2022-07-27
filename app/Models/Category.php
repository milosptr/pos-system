<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    CONST PARENT_0 = "ŠANK";
    CONST PARENT_1 = "KUHINJA";

    protected $fillable = ['name', 'order', 'print'];
    public $timestamps = true;
}
