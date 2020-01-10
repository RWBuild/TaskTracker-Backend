<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    public function record()
    {
        return $this->belongsTo(Record::class);
    }
    protected $fillable = [
        'user_id', 'name'
    ];
}
