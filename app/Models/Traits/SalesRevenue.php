<?php

namespace App\Models\Traits;

use Illuminate\Http\Request;
use App\Http\Filters\Sales\Date;
use App\Http\Filters\Sales\Sort;
use App\Http\Filters\Sales\Search;
use App\Http\Filters\Sales\Category;
use Illuminate\Database\Eloquent\Builder;

trait SalesRevenue
{
    protected $filters = [
        'date' => Date::class,
        'q' => Search::class,
        'category' => Category::class,
        'sort' => Sort::class,
    ];

    public function scopeFilter(Builder $builder, Request $request)
    {

        foreach($request->all() as $filter => $value) {
            if(!array_key_exists($filter, $this->filters)) {
                continue;
            }
            if(!class_exists($this->filters[$filter])) {
                continue;
            }

            $this->resolve($filter)->apply($builder, $value);
        }

        return $builder;
    }

    public function scopeFilterStats(Builder $builder, Request $request)
    {
        $builder->selectRaw('sum(total) as total')
            ->selectRaw('sum(case when status = 0 then total else 0 end) as refund')
            ->selectRaw('sum(total) - sum(case when status = 0 then total else 0 end) as income');

        foreach($request->all() as $filter => $value) {
            if(!array_key_exists($filter, $this->filters)) {
                continue;
            }
            if(!class_exists($this->filters[$filter])) {
                continue;
            }

            $this->resolve($filter)->apply($builder, $value);
        }

        return $builder;
    }

    public function resolve($filter)
    {
        return new $this->filters[$filter];
    }
}
