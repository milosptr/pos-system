<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'table_id' => $this->table_id,
            'table_name' => $this->table->name,
            'total' => $this->total,
            'order' => $this->order,
            'created_at' => Carbon::parse($this->created_at)->format('H:i'),
            'created_at_full' => $this->created_at,
            'time' => Carbon::parse($this->created_at)->format('H:i'),
        ];
    }
}
