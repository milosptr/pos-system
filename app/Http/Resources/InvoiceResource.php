<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'order' => $this->order,
            'total' => $this->total,
            'table_id' => $this->table_id,
            'table' => $this->table,
            'status' => $this->status,
            'note' => $this->note,
            'user' => $this->user,
            'created_at' => Carbon::parse($this->created_at)->format('d.m.Y H:s:i')
        ];
    }
}
