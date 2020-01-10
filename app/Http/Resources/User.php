<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Record as RecordResource;
use App\Http\Resources\OfficeTime as OfficeTimeResource;
class User extends JsonResource
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
            'names' => $this->names,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'has_checked' => $this->has_checked,
            'record' => RecordResource::collection($this->records),
            'office_time' => OfficeTimeResource::collection($this->office_times),
        ];
    }
}
