<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ThirdPartyInvoiceResource extends JsonResource
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
            'invoice_number' => $this->invoice_number,
            'table_name' => $this->table_name,
            'status' => $this->status,
            'order' => $this->order,
            'total' => $this->total,
            'payment_type' => $this->payment_type,
            'discount' => $this->discount,
            'created_at' => Carbon::parse($this->created_at)->format('d.m.Y H:i:s')
        ];
    }
}
