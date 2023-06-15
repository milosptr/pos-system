<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientBankAccount extends Model
{
    use HasFactory, HasUuid;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    protected $fillable = [
      'name',
      'bank_account',
      'reference_number',
      'active'
    ];

}
