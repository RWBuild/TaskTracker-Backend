<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $fillable = [
        'id',
        'name',
        'is_current',    
        'is_opened',
        'is_finished',
        'description',
        'status',
        'user_id', 
        'project_id',
        'start_date',
        'start_time',
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
