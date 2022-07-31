<?php

namespace App\Http\Filters\Invoice;

use Illuminate\Database\Eloquent\Builder;

class Waiter
{
    public static function apply(Builder $builder, $value)
    {
        return $builder->where('user_id', $value);
    }
}
