<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ThirdPartyOrderItemResource extends JsonResource
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
            'external_item_id' => $this->external_item_id,
            'name' => $this->name,
            'qty' => (float) $this->qty,
            'price' => $this->price,
            'unit' => $this->unit,
            'modifier' => $this->modifier,
            'print_station_id' => $this->print_station_id,
            'active' => $this->active,
        ];
    }
}
