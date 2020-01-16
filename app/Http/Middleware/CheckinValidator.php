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
 
        $to_morrow = now()->addDay();
        $yesterday = now()->subDay();
        $request->validate([
            'checkin_time' => 'required|date|after:'.$yesterday.'|before:'.$to_morrow 
        ]);
        

        $user = user();

        if ($user->has_checked) {
            return response([
                'success' => false,
                'message' => 'You have already checked in for today, Please check out first'
            ],409);
        }

        //verify if the user is not trying to double checkin in the same day
        if ($last_check=$user->office_times()->orderBy('id','desc')->first()) {
            if (Carbon::parse($last_check->checkin_time)->isToday()) {
                return response([
                    'success' => false,
                    'message' => 'Please Wait for tomorrow to checkin again'
                ],409);              
            }
        }

        return $next($request);
    }
}
