<?php

namespace App\Http\Filters\Sales;

use Illuminate\Database\Eloquent\Builder;

class Sort
{
    public static function apply(Builder $builder, $value)
    {
        $sort = explode('.', $value);
        $column = 'sales.'.$sort[0];
        $direction = $sort[1];
        return $builder->orderBy($column, $direction);
    }
}
