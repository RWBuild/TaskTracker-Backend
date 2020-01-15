<?php
namespace App\Classes;

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
                'message' => 'Task not found. please provide an existing task ',
                'status' => 404
            ]);
        }
        
        //check if the last entry type of this record has the same type as the type of the sent entry
        $last_record = $this->get_task_last_entry();
        if ($last_record) {

            if ($last_record->entry_type == $received_entry_type) {

                return to_object([
                    'success' => false,
                    'message' => "You can't ".strtoupper($received_entry_type)." again because the current ". 
                                 "status of this task is: ".strtoupper($last_record->entry_type),
                    'status' => 400
                ]);           
            }
        }


        //check if the record(task)  has entries in case user want to pause,resume or end
        if (in_array($received_entry_type,['pause','resume','end'])) {
            if (! $this->task_has_entries()) {
                return to_object([
                    'success' => false,
                    'message' => "Please START this task first before you ".strtoupper($received_entry_type)." it  ",
                    'status' => 400
                ]);
            }
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

        //creation of new entry
        $request = $this->request;

        $entry = $this->record->entries()->create([
            'entry_type' => $request->entry_type,
            'entry_time' => $request->entry_time            
        ]);

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

        $entry = $this->record->entries()->create([
            'entry_type' => $request->entry_type,
            'entry_time' => $request->entry_time            
        ]);

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
        
        //Check first if the last entry type is different to pause
        if ($last_entry->entry_type == 'pause') {
            return response([
                'success' => false,
                'message' => 'You can not end a task which is paused,please resume it first'
            ],400);        
        }

        //creation of the end entry
        $record = $this->record;
        $end_entry = $record->entries()->create([
            'entry_type' => $request->entry_type,
            'entry_time' => $request->entry_time,
        ]);
        
        //calculation of interval duration of a task from its previous entry to the paused one
        $end_entry->entry_duration = diffSecond($last_entry->entry_time,$end_entry->entry_time);
        $end_entry->save();

        //modify the task status: is_current,is_opened,is_finished
        $record->is_current = false;
        $record->is_opened = false;
        $record->is_finished = true;
        $record->save();

        return response([
            'success' => true,
            'entry' => new EntryResource($record->entries()->find($end_entry->id))
        ]);
        
    }
}