<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class Location extends JsonResource
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
          'longitude' => $this->longitude,
          'latitude' => $this->latitude,
          'radius' => $this->radius  
        ];
    }
}
