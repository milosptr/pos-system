<?php

namespace App\Http\Filters\Invoice;

use Illuminate\Database\Eloquent\Builder;

class Category
{
    public static function apply(Builder $builder, $value)
    {
        return $builder->where('category_id', $value);
    }
}
