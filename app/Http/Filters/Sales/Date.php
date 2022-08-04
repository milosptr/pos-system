<?php

namespace App\Http\Filters\Sales;

use Services\WorkingDay;
use Illuminate\Database\Eloquent\Builder;

class Date
{
    public static function apply(Builder $builder, $value)
    {
        $dates = WorkingDay::getWorkingDayForRange($value);

        $builder->whereBetween('sales.created_at', $dates);

        return $builder;
    }
}
