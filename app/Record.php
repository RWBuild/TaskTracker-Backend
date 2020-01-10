<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
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
}
