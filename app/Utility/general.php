
<?php

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Classes\EntryHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;



if (! function_exists('entry_helper')) {

  function entry_helper($record)
  {
    return new EntryHelper($record);
  }

}

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

function diffSecond($from_time, $to_time)
{
  $from_time = Carbon::parse($from_time);
  $to_time = Carbon::parse($to_time);
  $totalDuration = $to_time->diffInSeconds($from_time);
  return $totalDuration ;
}

function str_toSlug($value)
{
  return Str::slug($value);
}

function to_object($value)
{
  return ((Object)$value);
}