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
        // Use directly selected warehouse fields if available (from optimized index query)
        // Fall back to relationship for other endpoints that don't use the optimized query
        $warehouseName = $this->warehouse_name ?? $this->warehouse?->name;
        $warehouseUnit = $this->warehouse_unit ?? $this->warehouse?->unit;
        $categoryId = $this->category_id ?? $this->warehouse?->category_id;

        return [
            'id' => $this->id ?? uniqid('warehouse_status_', true),
            'warehouse_id' => $this->warehouse_id,
            'inventory_id' => $this->inventory_id,
            'import_quantity' => number_format($this->import_quantity ?? 0, 2, '.', ''),
            'sale_quantity' => number_format($this->sale_quantity ?? 0, 2, '.', ''),
            'previous_quantity' => number_format($this->previous_quantity ?? 0, 2, '.', ''),
            'recalculated_quantity' => number_format($this->recalculated_quantity ?? 0, 2, '.', ''),
            'quantity' => number_format($this->quantity ?? 0, 2, '.', ''),
            'type' => $this->type,
            'comment' => $this->comment,
            'warehouse' => [
                'name' => $warehouseName,
                'unit' => $warehouseUnit,
            ],
            'inventory' => $this->inventory_id ? $this->inventory : null,
            'date' => $this->date,
            'category_id' => $categoryId,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
