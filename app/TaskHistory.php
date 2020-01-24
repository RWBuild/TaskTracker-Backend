<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskHistory extends Model
{
    protected $fillable = [
        'history_time',
        'description',
        'record_id',
        'history_type'
   ];

   public function record()
   {
       return $this->belongsTo(Record::class);
   }
}
