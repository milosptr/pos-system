<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KitchenOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'orderable_type' => $this->orderable_type,
            'orderable_id' => $this->orderable_id,
            'table_name' => $this->table_name,
            'items' => $this->items->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'qty' => $item->qty,
                'modifier' => $item->modifier,
                'storno' => $item->storno,
                'is_done' => $item->is_done,
            ]),
            'ready_at' => $this->ready_at,
            'created_at' => $this->created_at,
        ];
    }
}
