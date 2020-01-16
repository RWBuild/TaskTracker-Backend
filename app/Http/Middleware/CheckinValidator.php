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

        //verify if the user is not trying to double checkin in the same day
        if ($last_check = $user->office_times()->orderBy('id','desc')->first()) {

            if (Carbon::parse($last_check->checkin_time)->isToday() 
            or $last_check->has_checked_in 
            or $user->has_checked ) 
            {
                return response([
                    'success' => false,
                    'message' => 'Please Wait for tomorrow to checkin again'
                ],400);              
            }
        }

        return $next($request);
    }
}
