<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MurugoUser extends Model
{

    protected $fillable = [
        'murugo_user_id', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
