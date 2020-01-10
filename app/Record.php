<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
<<<<<<< HEAD
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
=======
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
>>>>>>> 06ed39208117a3a70c3d8f66d58117a949af519e
}
