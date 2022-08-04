<?php

namespace App\Http\Filters\Sales;

use Illuminate\Database\Eloquent\Builder;

class Search {
    public static function apply(Builder $builder, $value)
    {
        $value = trim($value);

        return $builder->where('sales.name', 'like', "%{$value}%");
    }
}
