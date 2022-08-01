<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    CONST PARENT_0 = "ŠANK";
    CONST PARENT_1 = "KUHINJA";

    protected $fillable = ['name', 'parent_id', 'order', 'print', 'color'];
    public $timestamps = true;
}
