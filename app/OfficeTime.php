<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfficeTime extends Model
{
    protected $fillable = [
        'checkin_time',
        'checkout_time',
        'duration',
        'user_id'

    ];
}
