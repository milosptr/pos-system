<?php

namespace App\Http\Filters\Invoice;

use Illuminate\Database\Eloquent\Builder;

class Status
{
    public static function apply(Builder $builder, $value)
    {
        return $builder->where('status', $value);
    }
}
