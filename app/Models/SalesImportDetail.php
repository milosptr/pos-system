<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesImportDetail extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
      'filename',
    ];

    public $timestamps = true;

    public function sales()
    {
        return $this->hasMany(Sales::class, 'batch_id', 'id');
    }
}
