<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfficeTime extends Model
{
<<<<<<< HEAD
    protected $fillable = [
        'checkin_time',
        'checkout_time',
        'duration',
        'user_id'

    ];
=======
    public function user()
    {
        return $this->belongsTo(User::class);
    }
>>>>>>> 3f96c058b81868493adfb67bb02d4ad2d07c2938
}
