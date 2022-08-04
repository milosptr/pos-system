<?php

namespace App\Models\Traits;

use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Http\Filters\Invoice\Date;
use App\Http\Filters\Invoice\Inventory;
use App\Http\Filters\Inventory\Category;
use App\Http\Filters\Invoice\Waiter;
use App\Http\Filters\Invoice\Status;
use Illuminate\Database\Eloquent\Builder;

trait Revenue
{
    protected $filters = [
        'date' => Date::class,
        'waiter' => Waiter::class,
        'status' => Status::class,
        'inventory' => Inventory::class,
        'category' => Category::class,
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
        $builder->selectRaw('sum(total) as total')
            ->selectRaw('sum(case when status = 0 then total else 0 end) as refund')
            ->selectRaw('sum(total) - sum(case when status = 0 then total else 0 end) as income');

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
