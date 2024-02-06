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
            'id' => $this->id,
            'warehouse_id' => $this->warehouse_id,
            'inventory_id' => $this->inventory_id,
            'import_quantity' => $this->import_quantity,
            'sale_quantity' => $this->sale_quantity,
//            'date_import_quantity' => $this->date_import_quantity,
//            'date_sale_quantity' => $this->date_sale_quantity,
//            'date_quantity' => $this->date_quantity,
            'previous_quantity' => $this->previous_quantity,
            'quantity' => $this->quantity,
            'type' => $this->type,
            'comment' => $this->comment,
            'warehouse' => $this->warehouse,
            'inventory' => $this->inventory_id ? $this->inventory : null,
            'date' => $this->date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
