<?php

namespace App\Console\Commands;

use App\User;
use DateTime;
use DateTimezone;
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
            if(!$last_office_time->break_time)
            {
                $last_office_time->checkout_time = app_now();
                $last_office_time->duration = diffTime($last_office_time->checkin_time,$last_office_time->checkout_time,'%H:%I');
                $last_office_time->has_checked_out = true;
                $last_office_time->save();

                //check if current user task is not paused
                $user_current_task = $user->records()
                                        ->where('is_current',true)
                                        ->first();
                //pause the current task if it has been not paused
                $this->pauseUserCurrentTask($user_current_task);
            
                //then update user's has checked to false
                $user->has_checked = false;
                $user->save();
            }
            else 
            {
                $last_office_time->checkout_time = $last_office_time->break_time;
                $last_office_time->duration = diffTime($last_office_time->checkin_time,$last_office_time->checkout_time,'%H:%I');
                $last_office_time->has_checked_out = true;
                $last_office_time->save();
                
                //then update user's has checked to false
                $user->has_checked = false;
                $user->save();
            }
        }
        
        return $this->info("checkout updated successfully for all users who did not checkout");
    }

    public function pauseUserCurrentTask($task)
    {
        
        //when task exists
        if ($task) {
            //get the last entry of the task
            $last_entry = $task->entries->last();

            //pause if task is start or resume
            $this->info($last_entry->type);
            if (in_array($last_entry->entry_type,['start','resume'])) {
                $now = app_now();
                $task->entries()->create([
                    'entry_type' => 'pause',
                    'entry_time' => $now,
                    'entry_duration' => diffSecond($last_entry->entry_time,$now),
                    'auto_paused' => true
                ]);

                //change the status of the current task
                $task->status = 'pause';
                $task->save();
            }

            
        }
    }
}
