<?php
namespace App\Classes;

use DateTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Classes\Parents\EntryHelper;
use App\Http\Resources\Entry as EntryResource;

//Helper to call only on creation of an entry
class CreateEntryHelper extends EntryHelper
{

    public function __construct($record)
    {
        $this->record = $record;
        $this->request = request();
    }

    //to check if the current user can perform this operation
    public function user_is_allowed()
    {
        //check if the user has checked
        $user = user();
        if(! $user->has_checked)
        {
            return to_object([
                'success' => false,
                'message' => 'the user must checkin first to create an entry',
            ]);
        }

        // Check if the record(task) exist in db
        if (!$this->record) {
            return to_object([
                'success' => false,
                'message' => "Task not found or it may not belong to you. Please ".
                             "provide an existing task ",
                'status' => 404
            ]);
        }

        return to_object(['success' => true]);
    }
    
    //the main brain function to create an entry according to it type
    public function response()
    {
        //check if user is allowed
        $user_is_allowed = $this->user_is_allowed();

        if (! $user_is_allowed->success) {
           return response([
               'success' => false,
               'message' => $user_is_allowed->message
           ], 400);
        }
        //we check if record exist and avoid duplication of entry type
        $duplication_checker = $this->avoidEntryDuplication();

        if (! $duplication_checker->success) {
            return response([
                'success' => false,
                'message' => $duplication_checker->message
            ],$duplication_checker->status);
        }

        $request = $this->request;

        if ($request->entry_type == 'start') return $this->startTask();

        if ($request->entry_type == 'pause') return $this->pauseTask();

        if ($request->entry_type == 'resume') return $this->resumeTask();

        if ($request->entry_type == 'end') return $this->endTask();
    }

    public function avoidEntryDuplication ()
    {
       // Check if the user has sent a valid entry type
        if (!in_array($this->request->entry_type, $this->knownEntryType)) {
            return to_object([
                'success' => false,
                'message' => "Please make sure that you are sending: ".
                             "start,pause,resume or end as an entry type",
                'status' => 400
            ]);                   
        }
        
        $start_checker = $this->start_from_start_checker();
        if (!$start_checker->success) return $start_checker;
        
        //prerequist to check when a task has more than 1 entry
        if ($this->task_has_entries()) return $this->check_on_entry_exists();

        return to_object(['success' => true ]);
    }

    /** 
     * for preventing a user to : pause,resume or end
     * a task which has been not started 
    */
    public function start_from_start_checker() {

        if (in_array($this->request->entry_type,['pause','resume','end'])) {
            if (! $this->task_has_entries()) {
                return to_object([
                    'success' => false,
                    'message' => "Please START this task first before you ".
                                 strtoupper($this->request->entry_type)." it",
                    'status' => 400
                ]);
            }
        }
        return to_object(['success' => true ]);
    }

    /** 
     * to be called in create entry prerequists only when a task 
     * has more entries
    */
    public function check_on_entry_exists()
    {
        //check if the last entry type of this record has the same type as the type of the sent entry
        $prevent_same_entry_type = $this->prevent_same_entry_type();

        if (! $prevent_same_entry_type->success) return $prevent_same_entry_type;

        //prevent incomming entry time to be equal to the current last entry time of the task
        $last_entry = $this->lastEntry;

        if(!date_greater_than($this->request->entry_time, $last_entry->entry_time)) {
            return to_object([
                'success' => false,
                'message' => "Please the time of this entry must be greater than the ".
                            "previous entry time({$last_entry->entry_time})",
                'status' => 400
            ]);
        }

        //check first if the task has ended in case user want to : pause or resume
        if (in_array($this->request->entry_type,['resume','pause'])) {
            if ($last_entry->entry_type == 'end') {
                return to_object([
                    'success' => false,
                    'message' => 'You can no longer '.strtoupper($this->request->entry_type).'. '.
                                'this task has ended',
                    'status' => 400
                ]);
            }
        }
        return to_object(['success' => true ]);
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
        $entry = $this->create_entry();

        //change this  record status to start
        $this->change_record_status(['is_current' => true]);
        
        return $this->build_response($entry);
    }
    
    //helper function to be called when user want to pause a specific task
    public function pauseTask ()
    {
        $request = $this->request;
        $last_entry = $this->lastEntry;
        
        //creation of the paused entry
        $paused_entry = $this->create_entry();
        
        //calculation of interval duration of a task from its previous entry to the paused one
        $paused_entry->entry_duration = diffSecond($last_entry->entry_time,$paused_entry->entry_time);
        $paused_entry->save();

        //change this  record status to pause
        $this->change_record_status(['is_current' => true]);
        
        return $this->build_response($paused_entry);
    }

    //helper function to be called when user want to resume a specific task
    public function resumeTask ()
    {
        $request = $this->request;
        $last_entry = $this->lastEntry;
        
        //Check first if the last entry type is pause
        if ($last_entry->entry_type != 'pause') {
            return response([
                'success' => false,
                'message' => 'You can not resume a task that has been not paused'
            ],400);        
        }

        //Pause the current user task if exist before starting another one
        $this->pause_current_user_task();
        
        //create the entry
        $entry = $this->create_entry();

        //change this  record status to resume
        $this->change_record_status(['is_current' => true]);

        return $this->build_response($entry);
    }

    //helper function to be called when user want to end a specific task
    public function endTask ()
    {
        $end_entry = $this->create_end_task();

        //modify the task status: is_current,is_opened,is_finished and change record status to end
        $this->change_record_status([
            'is_current' => false,
            'is_opened' => false,
            'is_finished' => true
        ]);

        return $this->build_response($end_entry);
    }

    /*
      - create an end entry with entry_duration=0 when the previous entry of the task was pause 
      - other wise calculate the duration then save the end entry with that duration 
      - then return the created end entry
    */
    public function create_end_task()
    {
        $last_entry = $this->lastEntry;
        $end_entry;

        //if the last entry type is  pause, we set the entry duration to 0
        if ($last_entry->entry_type == 'pause') {
            $end_entry = $this->create_entry();            
        }
        else{

            $end_entry = $this->create_entry();
            
            //calculation of interval duration of a task from its previous entry to the paused one
            $end_entry->entry_duration = diffSecond($last_entry->entry_time,$end_entry->entry_time);
            $end_entry->save();
        }

        return $end_entry;
    }

    //this will save the task history then return the response
    public function build_response($entry)
    {
        //log task history
        record($this->record)->track_action("{$this->request->entry_type}_task");

        return response([
            'success' => true,
            'message' =>  strtoupper($this->request->entry_type).' entry successfully created',
            'entry' => new EntryResource($entry)
        ]);
    }
}