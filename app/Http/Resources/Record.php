<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Project as ProjectResource;
use App\Http\Resources\EntryCollection;

class Record extends JsonResource
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
            'name' => $this->name,
            'is_current' => $this->is_current,
            'is_opened' => $this->is_opened,
            'is_finished' => $this->is_finished,
            'description' => $this->description,
            'total_duration' => $this->entry_total_duration(),
            'user' => new UserResource($this->user),
            'project' => $this->when($this->project != null,new ProjectResource($this->project)),
            'entries' => new EntryCollection($this->entries),
        ];
    }

    public function entry_total_duration()
    {
        return $this->entries->sum('entry_duration');
    }
}
