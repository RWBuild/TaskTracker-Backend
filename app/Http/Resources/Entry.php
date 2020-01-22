<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Entry extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
          'id' => $this->id,
          'entry_type' => $this->entry_type,
          'entry_time' => $this->entry_time,
          'entry_duration' => $this->entry_duration,  
        ];
    }
}
