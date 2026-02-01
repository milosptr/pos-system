<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ThirdPartyOrderResource extends JsonResource
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
            'external_order_id' => $this->external_order_id,
            'table_id' => $this->table_id,
            'table_name' => $this->table_name,
            'items' => ThirdPartyOrderItemResource::collection($this->whenLoaded('items')),
            'total' => $this->total,
            'active_total' => $this->calculateActiveTotal(),
            'created_at' => Carbon::parse($this->created_at)->format('d.m.Y H:i:s'),
            'created_at_full' => $this->created_at,
        ];
    }

    /**
     * Calculate total for active items only.
     */
    private function calculateActiveTotal(): int
    {
        if (!$this->relationLoaded('items')) {
            return $this->total;
        }

        return $this->items
            ->where('active', 1)
            ->sum(fn($item) => (int) round($item->qty * $item->price));
    }
}
