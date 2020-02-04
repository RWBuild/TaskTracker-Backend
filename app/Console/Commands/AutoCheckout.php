<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoCheckout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkout:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check out the user automatically at mid-night daily';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info("Cron is working fine!");
        
        //get all authenticated users checked in
        
        $users = User::where('has_checked', true)->get();

        foreach($users as $user) 
        {
            // get one user's last office times
            $last_office_time = $user->office_times->last();

            // assign the last office time to break time
            $last_office_time->checkout_time = $last_office_time->break_time;
            $last_office_time->duration = diffTime($last_office_time->checkin_time,$last_office_time->checkout_time,'%H:%I');
            $last_office_time->has_checked_out = true;
            $last_office_time->save();
            
            //then update user's has checked to false
            $user->has_checked = false;
            $user->save();
        }
        
        return $this->info("checkout updated successfully for all users who did not checkout");
    }
}
