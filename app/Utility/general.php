
<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;


function getRouteName() {
    return Route::currentRouteName();
}

function isRouteName($routeName) {
    return getRouteName() == $routeName;
}


function user() 
{
  return Auth::user();
}

function isOwner($item)
{
  $user = user();
  return $user->id == $item->user_id or $user->hasRoles('superadministrator|projectmanager');
}

function diffTime($from_time, $to_time,  $format='YY-MM-dD %H:%I:%S')
{
  $from_time = Carbon::parse($from_time);
  $to_time = Carbon::parse($to_time);
  $totalDuration = $from_time->diff($to_time)->format($format);
  return $totalDuration ;
}

function str_toSlug($value)
{
  return Str::slug($value);
}