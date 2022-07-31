<?php

namespace App\Models\Traits;

use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Http\Filters\Invoice\Date;
use App\Http\Filters\Invoice\Waiter;
use App\Http\Filters\Invoice\Status;
use Illuminate\Database\Eloquent\Builder;

trait Revenue
{
    protected $filters = [
        'date' => Date::class,
        'waiter' => Waiter::class,
        'status' => Status::class,
    ];

    public function scopeFilter(Builder $builder, Request $request)
    {

      foreach($request->all() as $filter => $value)
        {
            if(!array_key_exists($filter, $this->filters)) continue;
            if(!class_exists($this->filters[$filter])) continue;

            $this->resolve($filter)->apply($builder, $value);
        }

        $builder->orderBy('id', 'desc');
        return $builder;
    }

    public function scopeFilterStats(Builder $builder, Request $request)
    {
        $builder->selectRaw('sum(invoices.total) as total')
            ->selectRaw('sum(case when invoices.status = 0 then invoices.total else 0 end) as refund')
            ->selectRaw('sum(total) - sum(case when invoices.status = 0 then invoices.total else 0 end) as income');

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
