<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
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
            'category_id' => $this->category_id,
            'category_name' => $this->category->name,
            'name' => $this->name,
            'description' => $this->description,
            'active' => $this->active,
            'sold_by' => $this->sold_by,
            'price' => $this->price,
            'sku' => $this->sku,
            'qty' => $this->qty,
            'color' => $this->color,
            'order' => $this->order,
            'unit' => $this->unit,
            'should_print' => $this->category->print
        ];
    }
}
