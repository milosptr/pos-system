<?php

namespace App\Http\Filters\Inventory;

use Illuminate\Database\Eloquent\Builder;

class OrderBy {
    public static function apply(Builder $builder, $value)
    {
        if(!$value)
            return $builder->orderBy('category_id', 'asc');

        $order = explode(':', $value);

        return $builder->orderBy($order[0], $order[1]);
    }
}
