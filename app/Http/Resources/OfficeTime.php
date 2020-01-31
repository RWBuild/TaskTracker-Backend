<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OfficeTime extends JsonResource
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
            'checkin_time' => $this->checkin_time,
            'checkout_time' => $this->checkout_time,
            'duration' => $this->duration,
            'break_time' => $this->break_time,
            'has_checked_in' => (Bool) $this->has_checked_in,
            'has_checked_out' => (Bool) $this->has_checked_out
        ];
    }
}
