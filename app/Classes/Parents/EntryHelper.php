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
     * To keep the current entry record
     * @var Record
     */
    public $record;

    /**
     * The entry types known by the application
     * @var Array
     */
    public $knownEntryType = [
        'start','pause','resume','end'
    ];

    /**
     * will keep the last entry of the current record
     * @var Record
     */
    public $lastEntry;

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
     * set the last entry of the taskbin $lastEntry
     * @return bool
    */
    public function task_has_entries ()
    {  
        if($this->record->entries->count() > 0) {
            $this->lastEntry = $this->get_task_last_entry();
            return true;
        }

        return false;
    }

    /** 
     * to get the last entry of a task
     * @param Record $record
     * @return Object
    */
    public function get_task_last_entry ($record = null)
    {
        if (!$record) {
            $record = $this->record;
        }
        return $record->load('entries')->entries->last();
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
     * @return Object
    */
    public function prevent_same_entry_type()
    {
        $last_entry = $this->lastEntry;
        if ($last_entry) {

            if ($last_entry->entry_type == $this->request->entry_type) {

                return to_object([
                    'success' => false,
                    'message' => "You can't ".strtoupper($this->request->entry_type)
                                 ." again because the current status of this task is: ".
                                 strtoupper($last_entry->entry_type),
                    'status' => 400
                ]);           
            }
        }

        return to_object(['success' => true]);
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
                'entry_time' => Carbon::now()->timezone('Africa/Cairo')->toDateTimeString(),
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
     * To create a new entry with [entry_type,entry_time]
     * comming from client(api consumer)
     * @return Object  Entry
    */
    public function create_entry()
    {
        return $this->record->entries()->create([
            'entry_type' => $this->request->entry_type,
            'entry_time' => $this->request->entry_time,
            'entry_duration' => 0
        ]); 
    }
}
