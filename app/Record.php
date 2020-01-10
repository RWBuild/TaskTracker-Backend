<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $fillable = [
<<<<<<< HEAD
        'project_id', 
        'user_id', 
        'name', 
        'description', 
        'is_curent', 
        'is_paused', 
        'is_completed'
   ];
=======
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

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
    
>>>>>>> 3f96c058b81868493adfb67bb02d4ad2d07c2938
}
