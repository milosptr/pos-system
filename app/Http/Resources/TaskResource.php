<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'company' => $this->company,
            'date' => isset($this->date) ? Carbon::parse($this->date)->format('d.m.Y') : '',
            'price' => $this->price,
            'message' => $this->message,
            'done' => $this->done,
            'type' => $this->type,
            'type_text' => $this->type === 1 ? 'Task' : 'Message',
            'created_at' => Carbon::parse($this->created_at)->format('d.m.Y H:i:s')
        ];
    }
}
