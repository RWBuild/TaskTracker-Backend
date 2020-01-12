
<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

function user() 
{
  return Auth::user();
}

function diffTime($from_time, $to_time,  $format='%YY-MM-dD %H:%I:%S')
{
  $from_time = Carbon::parse($from_time);
  $to_time = Carbon::parse($to_time);
  $totalDuration = $from_time->diff($to_time)->format($format);
  return $totalDuration ;
}