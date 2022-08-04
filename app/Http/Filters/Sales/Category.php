<?php

namespace App\Http\Filters\Sales;

use Illuminate\Database\Eloquent\Builder;

class Category
{
    public static function apply(Builder $builder, $value)
    {
        return $builder->where('sales.category_id', $value);
    }
}
