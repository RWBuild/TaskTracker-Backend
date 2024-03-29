<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    protected $fillable = [
        'record_id',
        'entry_type',
        'entry_time',
        'entry_duration',
    ];
    public function record()
    {
        return $this->belongsTo(Record::class);
    }
    

}
