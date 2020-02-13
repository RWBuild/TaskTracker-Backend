<?php

namespace App\Http\Controllers\ApiControllers;


use App\User;
use DateTime;
use Carbon\Carbon;
use App\OfficeTime;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\OfficeTimeCollection;
use App\Http\Resources\OfficeTime as OfficeTimeResource;

class OfficeTimeController extends Controller
{

   public function __construct()
   {
       $this->middleware('checkin_validator')->only('store');
   }
    
   //displaying the time a user has checkedin and checkedout
    public function index()
    {
        $office_times = user()->office_times;
        return new OfficeTimeCollection($office_times);
    }

    public function get_last_check()
    {
        $last_check = user()->office_times->last();
        
        if (!$last_check) {
           return response([
               'success' => true,
               'office_time' => null
           ]);
        }
        
        return response([
            'success' => true,
            'office_time' => new OfficeTimeResource($last_check)
        ]);
    }


    public function create()
    {
        //
    }

    // creating the time the user has checkedin
    public function store(Request $request)
    {
        $user = user();
        $officeTime = $user->office_times()->create([
            'checkin_time' => $request->checkin_time,
            'has_checked_in' => true
        ]);

        $user->update(['has_checked' => true]);

        return response([
            'success' => true,
            'message' => 'you have checked in successfully',
            'office_time' => new OfficeTimeResource($officeTime)
        ],200);
       
    }


    public function show(OfficeTime $officeTime)
    {
        //
    }

 
    public function edit(OfficeTime $officeTime)
    {
        //
    }

    //creating a time a user has checked out
    public function update(Request $request, $id)
    {
  
        $user = user();
        $officeTime = $user->office_times()->find($id);
        
        //when the check in time is not found before checking out
        if (!$officeTime) {
            return response([
                'success' => false,
                'message' => 'The office time time identifier was not found'
            ],404);
        }

        $request->validate([
            'checkout_time' => 'required|date|after:'.$officeTime->checkin_time
        ]);
       
        //when the user try to check out twice in a day
        if ($officeTime->has_checked_out) {
            return response([
                'success' => false,
                'message' => 'You have already checked out for today'
            ],409);
        }
        
        //calculation of the duration between checkout time and checkin time
        $officeTime->duration = diffTime($officeTime->checkin_time,$request->checkout_time,'%H:%I');
        $officeTime->has_checked_out = true;
        $officeTime->checkout_time = $request->checkout_time;
        $officeTime->save();
       
        $user->update(['has_checked' => false]);

        $officeTime = OfficeTime::find($officeTime->id);

        return response([
            'success' => true,
            'message' => 'you have successfully checked out',
            'office_time' => new OfficeTimeResource($officeTime)
        ],200);
     
        
    }

    public function destroy(OfficeTime $officeTime)
    {
        $user_last_office = user()->office_times->last();
        if ($user_last_office->id == $officeTime->id) {
            user()->update(['has_checked' => false]);
        }

        $officeTime->delete();

        return response([
            'success' => true,
            'message' => 'office successfully deleted'
        ],200);
    }

    public function break_time(Request $request){

        $user = user();
        $officeTime = $user->office_times()->get()->last();
        $office_time_id = $officeTime->id;

        $this->validate($request,[
            $break_time = $request->break_time
        ]);
        if(!Carbon::parse($request->break_time)->isToday())
        {
            return response()->json([
                'success' => false,
                'message' => 'the break time should be of today'
            ],400); 
        }
        if(!$office_time_id )
        {
            return response()->json([
                'success' => false,
                'message' => 'the office time is invalid'
            ],404);
        }
        if($break_time <= $officeTime->checkin_time)
        {
            return response()->json([
                'success' => false,
                'message' => 'the break time should not be less than the checkin time'
            ],400); 
        }
        $officeTime->break_time = $break_time;

        $officeTime->save();
        return response([
            'success' => true,
            'message' => 'it is break time',
            'break_time' => $officeTime->break_time
        ]);

    }
   
    //returning entry checkout time
    public function checkouTime($date)
    {
        $entry_checkout_time = entry_checkout_time($date);
        if($entry_checkout_time)
        {
            return response([
                'checkout_time' => $entry_checkout_time
            ],200);
        }
        else
        {
            return response([
                'checkout_time' => null
            ],404);
        }
    }
}
