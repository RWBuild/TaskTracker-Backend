<?php

namespace App;

use App\OauthAccessToken;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laratrust\Traits\LaratrustUserTrait;

class User extends Authenticatable
{
    use HasApiTokens,Notifiable,LaratrustUserTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'names', 'email', 'avatar','has_checked'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function AauthAcessToken(){
        return $this->hasMany(OauthAccessToken::class);
    }

    public function records()
    {
        return $this->hasMany(Record::class);
    }

    public function office_times()
    {
        return $this->hasMany(OfficeTime::class);
    }

    public function murugo_user()
    {
        return $this->hasOne(MurugoUser::class);
    }
      
    // public function roles()
    // {
    //     return $this->belongsToMany(Role::class)->withPivot('role_user');
    // }

}
