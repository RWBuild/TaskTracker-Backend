<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'user_id', 'name'
    ];
    public function records()
    {
        return $this->hasMany(Record::class);
    }
    
}
