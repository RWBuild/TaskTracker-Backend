<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    public function record()
    {
        return $this->belongsTo(Record::class);
    }
}
