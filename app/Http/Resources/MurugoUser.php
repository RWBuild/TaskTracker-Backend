<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MurugoUser extends JsonResource
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
            'murugo_user_id' => $this->murugo_user_id,
            'user' => $this->user
        ];
    }
}
