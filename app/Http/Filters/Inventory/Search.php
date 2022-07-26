<?php

namespace App\Http\Filters\Inventory;

use Illuminate\Database\Eloquent\Builder;

class Search {
    public static function apply(Builder $builder, $value)
    {
        $value = trim($value);

        return $builder->where('name', 'like', "%{$value}%");
    }
}
