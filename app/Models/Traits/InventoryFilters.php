<?php

namespace App\Models\Traits;

use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Http\Filters\Inventory\Search;
use App\Http\Filters\Inventory\OrderBy;
use App\Http\Filters\Inventory\Category;
use Illuminate\Database\Eloquent\Builder;

trait InventoryFilters {

    protected $filters = [
        'q' => Search::class,
        'category' => Category::class,
    ];

    public function scopeFilter(Builder $builder, Request $request)
    {
        $builder->select([
            'inventory.*'
        ]);

        foreach($request->all() as $filter => $value)
        {
            if(!array_key_exists($filter, $this->filters)) continue;
            if(!class_exists($this->filters[$filter])) continue;

            $this->resolve($filter)->apply($builder, $value);
        }

        $builder = OrderBy::apply($builder, $request->get('order_by'));

        return $builder;
    }

    public function resolve($filter)
    {
        return new $this->filters[$filter];
    }
}
