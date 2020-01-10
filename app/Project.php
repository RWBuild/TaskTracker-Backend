<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'user_id', 'name'
    ];
    public function record()
    {
        return $this->belongsTo(Record::class);
    }
    
}
