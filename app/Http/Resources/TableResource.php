<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TableResource extends JsonResource
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
            'name' => $this->name,
            'table_number' => $this->table_number,
            'total' => $this->orders->sum('total'),
            'area' => $this->area,
            'position_x' => $this->position_x,
            'position_y' => $this->position_y,
            'position_y_middle' => $this->position_y_middle,
            'position_x_middle' => $this->position_x_middle,
            'rotate' => $this->rotate,
            'size' => $this->size,
        ];
    }
}
