<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfficeTime extends Model
{
    protected $fillable = [
        'checkin_time',
        'checkout_time',
        'duration',
        'user_id',
        'has_checked_in',
        'has_checked_out',
        'break_time',
        'auto_paused'

    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
