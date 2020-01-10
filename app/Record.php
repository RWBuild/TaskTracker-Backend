<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $fillable = [
        'project_id', 
        'user_id', 
        'name', 
        'description', 
        'is_curent', 
        'is_paused', 
        'is_completed'
   ];
}
