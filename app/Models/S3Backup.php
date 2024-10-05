<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class S3Backup extends Model
{
    use HasFactory;
    protected $fillable = ['filename', 'path', 'size', 'is_uploaded', 'is_deleted'];

}
