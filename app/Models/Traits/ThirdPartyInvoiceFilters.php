<?php

namespace App\Models\Traits;

use Illuminate\Http\Request;
use App\Http\Filters\ThirdPartyInvoice\Date;
use App\Http\Filters\Invoice\Status;
use App\Http\Filters\ThirdPartyInvoice\PaymentType;
use Illuminate\Database\Eloquent\Builder;

trait ThirdPartyInvoiceFilters
{
    protected $filters = [
        'date' => Date::class,
        'status' => Status::class,
        'payment_type' => PaymentType::class,
    ];

    public function scopeFilter(Builder $builder, Request $request)
    {
        foreach($request->all() as $filter => $value)
        {
            if(!array_key_exists($filter, $this->filters)) continue;
            if(!class_exists($this->filters[$filter])) continue;

            $this->resolve($filter)->apply($builder, $value);
        }

        return $builder;
    }

    public function resolve($filter)
    {
        return new $this->filters[$filter];
    }
}
