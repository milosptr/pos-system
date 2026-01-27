<?php

namespace App\Http\Filters\ThirdPartyInvoice;

use Illuminate\Database\Eloquent\Builder;

class PaymentType
{
    public static function apply(Builder $builder, $value)
    {
        return $builder->where('payment_type', $value);
    }
}
