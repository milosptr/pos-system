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
            'disableRefund' => true,
            'refund_reason' => isset($this->refundReason) ? $this->refundReason->name : '',
            'user' => [
              'user_id' => $this->user->id,
              'name' => $this->user->name,
            ],
            'location' => $this->table->tableLocation,
            'tax' => number_format($this->total * 0.166667, 2, ',', '.'),
            'created_at' => Carbon::parse($this->created_at)->format('d.m.Y H:i:s')
        ];
    }
}
