<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $fillable = [
        'id',
         'project_id', 
         'user_id', 
         'name', 
         'description', 
         'is_curent', 
         'is_paused', 
         'is_completed'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entries()
    {
        return $this->hasMany(Entry::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
}
