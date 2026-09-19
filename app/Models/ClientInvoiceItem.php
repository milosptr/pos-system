<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientInvoiceItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
      'client_invoice_id',
      'position',
      'sku',
      'name',
      'quantity',
      'unit',
      'unit_price',
      'unit_price_gross',
      'net_amount',
      'vat_rate'
    ];

    protected $casts = [
      'quantity' => 'float',
      'unit_price' => 'float',
      'unit_price_gross' => 'float',
      'net_amount' => 'float',
      'vat_rate' => 'float'
    ];

    public function invoice()
    {
        return $this->belongsTo(ClientInvoice::class, 'client_invoice_id');
    }
}
