<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
         'name','active'
    ];
    public function records()
    {
        return $this->hasMany(Record::class);
    }
    
}
