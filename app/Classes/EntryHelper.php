<?php
namespace App\Classes;

use DateTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Resources\Entry as EntryResource;

class EntryHelper 
{

    public $request,
           $record;

    public function __construct($record)
    {
        $this->request = request();
        $this->record = $record;
    }


    //to check if a specific task has entries
    public function task_has_entries ()
    {
        return $this->record->entries->count() > 0 ? true : false;
    }

    //to get the last entry of a task
    public function get_task_last_entry ()
    {
        return $this->record->entries()->orderBy('id','desc')->first();
    }

    //checking if the current time is not less than the previous time
    public function time_validation()
    {
      $last_entry_time = new DateTime($this->get_task_last_entry()->entry_time);
      $current_entry = new DateTime($this->request->entry_time);
      if($current_entry <= $last_entry_time)
      {
        return to_object([
            'success' => false,
            'message' => "you can't create an entry which has less time than the previous entry",
            'status' => 400
        ]); 
      }

      return to_object(['success' => true ]);
       
    }

    //Will help to update the current record status at each and every record entry 
    public function change_record_status($data = [])
    {
        $data['status'] = $this->request->entry_type;
        $this->record->update($data);
    }

    //will help to pause the current task(record) before starting or resumming another task
    public function pause_current_user_task()
    {
        $user = user();
        $current_record = $user->records()->where('is_current',true)->
        where('id','!=',$this->record->id)->first();

        if ($current_record) {

            $last_entry = $current_record->entries()->orderBy('id','desc')->first();

            //creation of the paused entry
            $paused_entry = $current_record->entries()->create([
                'entry_type' => 'pause',
                'entry_time' => Carbon::now()->timezone('Africa/Cairo')->toDateTimeString(),
            ]);
        
            //calculation of interval duration of a task from its previous entry to the paused one
            
            $paused_entry->entry_duration = diffSecond($last_entry->entry_time,$paused_entry->entry_time);
            $paused_entry->save();

            $current_record->status = 'pause';
            $current_record->is_current = false;
            $current_record->save();


        }
    }

    public function avoidEntryDuplication ()
    {
       $knownEntryType = ['start','pause','resume','end'];
       $received_entry_type = $this->request->entry_type;

        // Check if the user has sent a valid entry type
        if (!in_array($received_entry_type, $knownEntryType)) {
            return to_object([
                'success' => false,
                'message' => "Please make sure that you are sending: start,pause,resume or end as an entry type",
                'status' => 400
            ]);                   
        }
        
        // Check if the record(task) exist in databse
        if (!$this->record) {
            return to_object([
                'success' => false,
                'message' => 'Task not found or it may not belong to you. Please provide an existing task ',
                'status' => 404
            ]);
        }
        
        //check if the last entry type of this record has the same type as the type of the sent entry
        $last_record = $this->get_task_last_entry();
        
        $prevent_same_entry_type = $this->prevent_same_entry_type($last_record);

        if (! $prevent_same_entry_type->success) {
            return $prevent_same_entry_type;
        }

        //check if the record(task)  has entries in case user want to pause,resume or end
        $entry_status_time_checker = $this->entry_status_and_time_checker();

        if (! $entry_status_time_checker->success) {
            return $entry_status_time_checker;
        }

        //check first if the task has ended in case user want to : pause or resume
        if (in_array($received_entry_type,['resume','pause'])) {
            if ($last_record->entry_type == 'end') {
                return to_object([
                    'success' => false,
                    'message' => 'You can no longer '.strtoupper($received_entry_type).'. this task has ended',
                    'status' => 400
                ]);
            }
        }

        
        return to_object(['success' => true ]);
    }



    //to prevent simultaneous entries having same type
    public function prevent_same_entry_type($last_record)
    {
        if ($last_record) {

            if ($last_record->entry_type == $this->request->entry_type) {

                return to_object([
                    'success' => false,
                    'message' => "You can't ".strtoupper($this->request->entry_type)." again because the current ". 
                                 "status of this task is: ".strtoupper($last_record->entry_type),
                    'status' => 400
                ]);           
            }
        }

        return to_object(['success' => true]);
    }

    /*
     - to check if the record has been started before to : pause,resume or end it.
     - entry time checker: to prevent the creation of an entry at a time which is 
       before the previous entry time
    */
    public function entry_status_and_time_checker()
    {
        //check if the record(task)  has entries in case user want to pause,resume or end
        if (in_array($this->request->entry_type,['pause','resume','end'])) {
            if (! $this->task_has_entries()) {
                return to_object([
                    'success' => false,
                    'message' => "Please START this task first before you ".strtoupper($this->request->entry_type)." it",
                    'status' => 400
                ]);
            }

            //validating if the time of the current entry is not less than the time of the last entry
            $time_validation = $this->time_validation();
                if(!$time_validation->success)
                {
                    return to_object([
                        'success' => false,
                        'message' => $time_validation->message,
                        'status' => 400
                    ]);
                }
        }
        return to_object(['success' => true]);
    }

    //helper function to be called when user want to start a specific task
    public function startTask ()
    {
        //check first if this is not the first entry of this task
        if ($this->task_has_entries()) {
            return response([
                'success' => false,
                'message' => 'You have already started this task'
            ],400);
        }

        //Pause the current user task if exist before starting another one
        $this->pause_current_user_task();

        //creation of new entry
        $request = $this->request;

        $entry = $this->record->entries()->create([
            'entry_type' => $request->entry_type,
            'entry_time' => $request->entry_time            
        ]);

        //change this  record status to start
        $this->change_record_status(['is_current' => true]);

        return response([
            'success' => true,
            'entry' => new EntryResource($entry)
        ]);


    }
    
    //helper function to be called when user want to pause a specific task
    public function pauseTask ()
    {
        $request = $this->request;
        $last_entry = $this->get_task_last_entry();
        
        //creation of the paused entry
        $paused_entry = $this->record->entries()->create([
            'entry_type' => $request->entry_type,
            'entry_time' => $request->entry_time,
        ]);
        
        //calculation of interval duration of a task from its previous entry to the paused one
        $paused_entry->entry_duration = diffSecond($last_entry->entry_time,$paused_entry->entry_time);
        $paused_entry->save();

        //change this  record status to pause
        $this->change_record_status(['is_current' => true]);

        return response([
            'success' => true,
            'entry' => new EntryResource($this->record->entries()->find($paused_entry->id)),
            
        ]);
    }

    //helper function to be called when user want to resume a specific task
    public function resumeTask ()
    {
        $request = $this->request;
        $last_entry = $this->get_task_last_entry();
        
        //Check first if the last entry type is pause
        if ($last_entry->entry_type != 'pause') {
            return response([
                'success' => false,
                'message' => 'You can not resume a task that has been not paused'
            ],400);        
        }

        //Pause the current user task if exist before starting another one
        $this->pause_current_user_task();

        $entry = $this->record->entries()->create([
            'entry_type' => $request->entry_type,
            'entry_time' => $request->entry_time            
        ]);

        //change this  record status to resume
        $this->change_record_status(['is_current' => true]);

        return response([
            'success' => true,
            'entry' => new EntryResource($entry)
        ]);
    }

    //helper function to be called when user want to end a specific task
    public function endTask ()
    {
        $request = $this->request;
        $last_entry = $this->get_task_last_entry();
        
        //creation of the end entry
        $record = $this->record;
        $end_entry;
        //if the last entry type is  pause, we set the entry duration to 0
        if ($last_entry->entry_type == 'pause') {
            $end_entry = $record->entries()->create([
                'entry_type' => $request->entry_type,
                'entry_time' => $request->entry_time,
                'entry_duration' => 0
            ]);             
        }
        else{

            $end_entry = $record->entries()->create([
                'entry_type' => $request->entry_type,
                'entry_time' => $request->entry_time
            ]);
            
            //calculation of interval duration of a task from its previous entry to the paused one
            $end_entry->entry_duration = diffSecond($last_entry->entry_time,$end_entry->entry_time);
            $end_entry->save();
        }


        //modify the task status: is_current,is_opened,is_finished and change record status to end
         $this->change_record_status([
            'is_current' => false,
            'is_opened' => false,
            'is_finished' => true
        ]);

        return response([
            'success' => true,
            'entry' => new EntryResource($record->entries()->find($end_entry->id))
        ]);
        
    }


}