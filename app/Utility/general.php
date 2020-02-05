
<?php

// status codes
// ==============
// bad request => 400
// new created object => 201
// success => 200
// forbiden =>403
// not found =>404
// delete =>204

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Classes\TaskHistoryHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Exceptions\TaskTrackerException;


function task_history_helper() {
  return new TaskHistoryHelper();  
}

function record($record) {
  return task_history_helper()
          ->get_record($record);
}

function getRouteName() {
  return Route::currentRouteName();
}

function isRouteName($routeName) {
  return getRouteName() == $routeName;
}


function user() {
  return Auth::user();
}

function isOwner($item) {
  $user = user();
  return $user->id == $item->user_id or $user->hasRole('superadministrator|projectmanager');
}

function diffTime($from_time, $to_time,  $format='YY-MM-dD %H:%I:%S') {
  $from_time = Carbon::parse($from_time);
  $to_time = Carbon::parse($to_time);
  $totalDuration = $from_time->diff($to_time)->format($format);
  return $totalDuration ;
}

function diffSecond($from_time, $to_time) {
  $from_time = Carbon::parse($from_time);
  $to_time = Carbon::parse($to_time);
  $totalDuration = $to_time->diffInSeconds($from_time);
  return $totalDuration ;
}

function str_toSlug($value) {
  return Str::slug($value);
}

function to_object($value) {
  return ((Object)$value);
}

function buildException($error_msg, $status = 400) {
  return new TaskTrackerException($error_msg, $status);
}

function trigger_exception($error_msg,$status = 400) {
  throw buildException($error_msg, $status);
}

function triggerExceptionWith($error_msg) {
  return $exception = buildException($error_msg);
}


//get the current time
function app_now() {
  return Carbon::now()
                 ->timezone('Africa/Cairo')
                 ->toDateTimeString();
}

/**
 * return the place that an item occupies in a given collection
 * based on a given id of that item
 * Eg: $data = collect([{id:12,name:'A'}, {id:13,name:'B'} ]), 
 * Eg: $id = 13
 * In the above case the item_index($data, $id) = 2
 */
function item_index($data,$id) {
  $index =  $data->search(function($item) use($id){
    return $item->id == $id;
  });

  return $index === false? 0: $index + 1;
}

/**
 * return a given number to its ordinal
 * Eg: return 1 to 1st and 5 to 5th
 */
function number_to_ordinal($number) {
   if ($number == 0) {
      return '';
   }

   $nb_string = (String) $number;
   $last_number = substr($nb_string, -1);

   $sufix = 'th';

   if ($last_number == '1'){
      $sufix = 'st';
   }
   else if($last_number == '2'){
    $sufix = 'nd';
   }
   else if($last_number == '3'){
    $sufix = 'rd';
   }
   

   return $nb_string.''.$sufix;


}

/**
 * will return the place in ordinal format that a given entry occupies 
 * in a collection list of entries of a specific task
 * Eg: if the entry is the second of a given task
 * This will return : 2nd
 */
function entry_index($data,$id) {
    return number_to_ordinal(item_index($data,$id));
}

/**
 * This will convert a given array to string then 
 * join the item with a specific caracter
 */
function arrayToString($data = [],$joinWith = ',') {
  return implode($joinWith, $data);
}

/**
 * This will turn a normal array to collection
 * and all child arrays of this array will be casted 
 * to object
 */
function toCollection($data = []) {
  return collect($data)->map(function($item){
    return (Object) $item;
  });
}

/**
 * This will turn a collection to array
 * Then turn all the inner object to array
 * @param Collection $data
 * @return Array
 */
function collectionToArray($data) {

  //collection to array
  $data = $data->toArray();
  $casted_array = [];

  //turn all child object to array
  foreach ($data as $key => $value) {
    $casted_array[] = (Array) $data[$key];
  }
  
  return $casted_array;
}

//to check if dateA is greater than dateB
function date_greater_than($dateA, $dateB, $addEqual=false) {
    $dateA = Carbon::parse($dateA);
    $dateB = Carbon::parse($dateB);

    if ($addEqual) {
      if ($dateA->gte($dateB)) return true;
    }else {
      if ($dateA->gt($dateB)) return true;
    }
    
    return false;
}





