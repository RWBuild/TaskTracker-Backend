<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Record as RecordResource;

class TaskHistory extends JsonResource
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
            'description' => $this->description,
            'history_type' => $this->history_type,
            'history_time' => $this->history_time,
            'record' => $this->when(isRouteName('task_histories.show'), new RecordResource($this->record)),
        ];
    }
}
