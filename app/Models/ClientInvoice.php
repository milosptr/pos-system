<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientInvoice extends Model
{
    use HasFactory, HasUuid;

    const STATUS_PENDING = 0;
    const STATUS_PAID = 1;
    const STATUS_CANCELLED = 2;

    protected $fillable = [
      'client_account',
      'reference_number',
      'currency_date',
      'transaction_date',
      'amount',
      'status'
    ];

    public function clientAccount()
    {
        return $this->belongsTo(ClientBankAccount::class, 'client_account');
    }
}
