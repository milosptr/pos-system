<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseStatusResource extends JsonResource
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
            'id' => $this->id ?? uniqid('warehouse_status_', true),
            'warehouse_id' => $this->warehouse_id,
            'inventory_id' => $this->inventory_id,
            'import_quantity' => number_format($this->import_quantity, 2, '.'),
            'sale_quantity' => number_format($this->sale_quantity, 2, '.'),
            'previous_quantity' => number_format($this->previous_quantity, 2, '.'),
            'recalculated_quantity' => number_format($this->recalculated_quantity, 2, '.'),
            'quantity' => number_format($this->quantity, 2, '.'),
            'type' => $this->type,
            'comment' => $this->comment,
            'warehouse' => $this->warehouse,
            'inventory' => $this->inventory_id ? $this->inventory : null,
            'date' => $this->date,
            'category_id' => $this->warehouse->category_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
