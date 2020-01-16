<?php

namespace App\Http\Controllers\ApiControllers;


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

    public function index()
    {
        $office_times = user()->office_times;
        return new OfficeTimeCollection($office_times);
    }


    public function create()
    {
        //
    }


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
            'message' => 'successfully checked in',
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


    public function update(Request $request, $id)
    {
  
        $user = user();
        $officeTime = $user->office_times()->find($id);

        if (!$officeTime) {
            return response([
                'success' => false,
                'message' => 'Checkout identifer not valid'
            ],404);
        }

        $request->validate([
            'checkout_time' => 'required|date|after:'.$officeTime->checkin_time
        ]);
       
        if (!$user->has_checked and $officeTime->has_checked_out) {
            return response([
                'success' => false,
                'message' => 'You have already checked out for today'
            ],409);
        }

        $officeTime->duration = diffTime($officeTime->checkin_time,$request->checkout_time,'%H:%I');
        $officeTime->has_checked_out = true;
        $officeTime->checkout_time = $request->checkout_time;
        $officeTime->save();
       
        $user->update(['has_checked' => false]);

        $officeTime = OfficeTime::find($officeTime->id);

        return response([
            'success' => true,
            'message' => 'successfully checked out',
            'office_time' => new OfficeTimeResource($officeTime)
        ],200);
     
        
    }

    public function destroy(OfficeTime $officeTime)
    {
        //
    }
}
