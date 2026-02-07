<?php

namespace App\Http\Filters\ThirdPartyInvoice;

use Services\WorkingDay;
use Illuminate\Database\Eloquent\Builder;

class Date
{
    public static function apply(Builder $builder, $value)
    {
        $dates = WorkingDay::getWorkingDayForRange($value);

        $builder->whereRaw(
            'COALESCE(invoiced_at, created_at) BETWEEN ? AND ?',
            $dates
        );

        return $builder;
    }
}
