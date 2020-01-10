<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
<<<<<<< HEAD
    public function record()
    {
        return $this->belongsTo(Record::class);
    }
=======
    protected $fillable = [
        'id',
        'record_id',
        'entry_type',
        'entry_time',
        'entry_duration'
    ];

>>>>>>> 06ed39208117a3a70c3d8f66d58117a949af519e
}
