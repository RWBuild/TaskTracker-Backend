<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    protected $fillable = [
        'id',
        'record_id',
        'entry_type',
        'entry_time',
        'entry_duration'
    ];

}
