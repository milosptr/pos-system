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
      'sef_id',
      'invoice_number',
      'client_account',
      'supplier_pib',
      'supplier_bank_account',
      'reference_number',
      'payment_model',
      'issue_date', // datum izdavanja
      'payment_deadline', // datum valute
      'transaction_date', // datum prometa
      'processed_at', // datum transakcije
      'amount',
      'status'
    ];

    protected $casts = [
      'sef_id' => 'integer'
    ];

    public function clientAccount()
    {
        return $this->belongsTo(ClientBankAccount::class, 'client_account');
    }

    public function items()
    {
        return $this->hasMany(ClientInvoiceItem::class, 'client_invoice_id');
    }
}
