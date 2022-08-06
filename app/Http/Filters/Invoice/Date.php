<?php

namespace App\Http\Filters\Invoice;

use Services\WorkingDay;
use Illuminate\Database\Eloquent\Builder;

class Date
{
    public static function apply(Builder $builder, $value)
    {
        $dates = WorkingDay::getWorkingDayForRange($value);

        $builder->whereBetween('created_at', $dates);

        return $builder;
    }
}
