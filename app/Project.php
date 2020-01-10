<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
<<<<<<< HEAD
    public function record()
    {
        return $this->belongsTo(Record::class);
    }
=======
    protected $fillable = [
        'user_id', 'name'
    ];
>>>>>>> 06ed39208117a3a70c3d8f66d58117a949af519e
}
