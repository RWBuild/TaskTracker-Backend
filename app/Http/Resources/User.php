<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Record as RecordResource;
use App\Http\Resources\OfficeTime as OfficeTimeResource;
use App\Http\Resources\Role as RoleResource;
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
            'id' => $this->id,
            'names' => $this->names,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'has_checked' => (Bool) $this->has_checked,
            //'records' => RecordResource::collection($this->records),
            //'office_times' => OfficeTimeResource::collection($this->office_times),
            'roles' => RoleResource::collection($this->roles)
    
        ];
    }
}
