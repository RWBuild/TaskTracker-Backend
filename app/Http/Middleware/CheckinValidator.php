<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;

class CheckinValidator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
 
        $request->validate([
            'checkin_time' => 'required|date'
        ]);

        if (! Carbon::parse($request->checkin_time)->isToday()) {
            return response([
                'success' => false,
                'message' => 'The check in date must be today'
            ]);
        }
        
        $user = user();
        if ($user->has_checked) {
            return response([
                'success' => false,
                'message' => 'You have already checked in'
            ],400);            
        }

        //verify if the user is not trying to double checkin in the same day
        if ($last_check = $user->office_times()->orderBy('id','desc')->first()) {
<<<<<<< HEAD
            if (Carbon::parse($last_check->checkin_time)->isToday()) {
=======

            if (Carbon::parse($last_check->checkin_time)->isToday() 
            and $last_check->has_checked_in ) 
            {
>>>>>>> d0c533be5795ea5844d6c7ce269fa3a51750e94b
                return response([
                    'success' => false,
                    'message' => 'Please Wait for tomorrow to checkin again'
                ],400);              
            }
        }

        return $next($request);
    }
}
