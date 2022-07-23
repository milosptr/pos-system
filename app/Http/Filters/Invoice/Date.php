<?php

namespace App\Http\Filters\Invoice;

use Illuminate\Database\Eloquent\Builder;

class Date
{
    public static function apply(Builder $builder, $value)
    {
        $dates = explode(' to ', $value);

        $builder->whereBetween('created_at', $dates);

        return $builder;
    }
}
