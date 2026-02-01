<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ThirdPartyOrderItem extends Model
{
    use HasUuid;

    protected $fillable = [
        'third_party_order_id',
        'external_item_id',
        'name',
        'qty',
        'price',
        'unit',
        'modifier',
        'print_station_id',
        'active',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'price' => 'integer',
        'active' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(ThirdPartyOrder::class, 'third_party_order_id');
    }
}
