<?php

namespace App\Http\Filters\Invoice;

use Illuminate\Database\Eloquent\Builder;

class Inventory
{
    public static function apply(Builder $builder, $value)
    {
        return $builder->where('inventory_id', $value);
    }
}
