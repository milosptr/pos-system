<?php

namespace App\Http\Filters\Inventory;

use Illuminate\Database\Eloquent\Builder;

class Category {
    public static function apply(Builder $builder, $value)
    {
        if($value)
            return $builder->where('category_id', $value);
        return $builder;
    }
}
