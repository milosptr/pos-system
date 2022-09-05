<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryExportResource extends JsonResource
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
            'category_name' => $this->category->name,
            'name' => $this->name,
            'description' => $this->description,
            'active' => $this->active,
            'sold_by' => $this->sold_by,
            'price' => $this->price,
            'sku' => $this->sku,
            'qty' => (int) $this->qty,
            'color' => $this->color,
            'order' => $this->order,
            'should_print' => $this->category->print
        ];
    }
}
