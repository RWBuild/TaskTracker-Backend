<?php
namespace App\Classes\Parents;

use Carbon\Carbon;

class EntryHelper 
{
    /**
     * To keep the current request
     * @var Request
     */
    public $request;

    /**
     * To keep the current record
     * @var Record
     */
    public $record;

    /**
     * To keep the current entry: 
     * used for update and delete entry
     * @var Entry
     */
    public $entry;

    /**
     * The entry types known by the application
     * @var Array
     */
    public $knownEntryType = [
        'start','pause','resume','end'
    ];

    /**
     * will keep the last entry of the current record
     * @var Entry
     */
    public $lastEntry;

    /**
     * will keep the previous entry based on the 
     * current one
     * @var Entry
     */
    public $previousEntry;

    /**
     * will keep the next entry based on the 
     * current one
     * @var Entry
     */
    public $nextEntry;

    /** 
     * to check if a record of the entry in proccess
     * is current
     * @return bool
    */
    public function is_current ()
    {  
        return $this->record->is_current;
    }
    
    /** 
     * to check if the curent task(record) has entries
     * return error with provided msg or return bool
     * @var $msg
     * @return bool|Response
    */
    public function task_has_entries ($msg = null,$when = false)
    {  
        if($this->record->entries->count() > 0) {
            if ($when) {
                $this->build_error($msg);
            }

            return true;
        }

        if ($msg and $when === false) {
            $this->build_error($msg);
        }

        return false;
    }

    /** 
     * to get the last entry of the current task
     * or of a specific task(record)
     * @param Record $record
     * @return Object
    */
    public function get_task_last_entry ($record = null)
    {
        if (!$record) {
            $record = $this->record;
        }

        $last = $record->load('entries')->entries->last();

        //we set lastEntry only if current entry belongs to record
        if ($record->id == $this->record->id) {
            $this->lastEntry = $last;
        }

        return $last;
    }
  
    /** 
     * Will help to update the current record status depending on
     * incomming record entry ,In case status is not provided in 
     * $data, it  will take the request entry_type as record status
     * @param Array $data
     * @param int $status
     * @return void
    */
    public function change_record_status($data = [])
    {
        $status = "";

        if(!isset($data['status'])) {
            $status = $this->request->entry_type;
        }

        $data['status'] = $status;
        $this->record->update($data);
    }
 
    /** 
     * to prevent simultaneous entries having same type
     * the condition will be based on the comming request
     * entry type and the current entry type of the record
     * @example if the current entry type was: start, comming entry can no longer be start
     * @param Record $record
    */
    public function prevent_same_entry_type()
    {
        $last_entry = $this->lastEntry;
        if ($last_entry) {

            if ($last_entry->entry_type == $this->request->entry_type) {

                $this->build_error([
                    'message' => "You can't ".strtoupper($this->request->entry_type)
                                 ." again because the current status of this task is: ".
                                 strtoupper($last_entry->entry_type),
                ]);           
            }
        }

    }
 
    /** 
     * will help to pause another current task(record) of the auth
     * user before  he can start, resume a new one
     * This will be used again when user want to delete a paused entry
     * or an ended entry of task
     * @return void
    */
    public function pause_current_user_task()
    {
        //stop the process if this record is the current one
        if ($this->is_current()) return;

        $user = user();
        $other_record = $user->records()->where('is_current',true)
                               ->where('id','!=',$this->record->id)
                               ->first();

        if ($other_record) {
            //get last entry of this record
            $last_entry = $this->get_task_last_entry($other_record);
             
            //change only the record is current to false when its current status is pause
            if ($last_entry->entry_type == 'pause') {
                $other_record->is_current = false;
                return $other_record->save();
            }

            //creation of the paused entry
            $paused_entry = $other_record->entries()->create([
                'entry_type' => 'pause',
                'entry_time' => app_now(),
            ]);
        
            //calculation of interval duration of a task from its previous entry to the paused one
            
            $paused_entry->entry_duration = diffSecond($last_entry->entry_time,$paused_entry->entry_time);
            $paused_entry->save();

            $other_record->status = 'pause';
            $other_record->is_current = false;
            $other_record->save();
        }
    }
   
    /** 
     * To check if the current entry is the last one of the record
     * @var Sring $msg : message to display 
     * @var Bool $when : display message if consition test is equal to $when
     * @return Bool|Response
    */
    public function is_last_entry($msg = null, $when = false)
    {
        $last_entry = $this->get_task_last_entry();

        if ($last_entry->id == $this->entry->id) {
            if ($when) {
               $this->build_error($msg);
            }
            return true;
        }

        if ($when === false) {
            $this->build_error($msg);
         }

        return false;
    }

    /** 
     * To check if the incomming time is not future time
     * for preventing user to create or update an entry with 
     * a future time
     * @return Object 
    */
    public function time_future_checker()
    {
        $now = app_now();
        if (date_greater_than($this->request->entry_time,$now)) {
            return $this->build_error([
                'message' => 'Please an entry time can not be a future time'
            ]);
        }
    }

    /** 
     * get the next entry of a task based on the current
     * @return Entry
    */
    public function get_next_entry()
    {
        $current_entry = $this->entry;

        $next_entry = $this->record->entries
                           ->first(function($entry) use($current_entry) {
                                return $current_entry->id < $entry->id;
                           });
        $this->nextEntry = $next_entry;

        return $next_entry;
    }

    /** 
     * get the previous entry of a task based on the current
     * @return Entry
    */
    public function get_previous_entry()
    {
        $current_entry = $this->entry;
        $previous_entry = $this->record->entries
                            ->last(function($entry) use($current_entry) {
                                return $current_entry->id > $entry->id;
                            });
        $this->previousEntry = $previous_entry;

        return $previous_entry;
    }

    /** 
     * To create a new entry with [entry_type,entry_time]
     * comming from client(api consumer)
     * @return Object  Entry
    */
    public function create_entry()
    {
        return $this->record->entries()->create([
            'entry_type' => $this->request->entry_type,
            'entry_time' => $this->request->entry_time,
            'entry_duration' => null
        ]); 
    }

    /** 
     * this will throw an exception
     * @return throw
    */
    public function build_error($data,$status = null)
    { 
        if(is_string($data)) {
            trigger_exception($data, $status);
        } else {
            $data = to_object($data);
            trigger_exception($data->message, $status);
        }
        
    }
}
