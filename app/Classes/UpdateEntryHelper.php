<?php
namespace App\Classes;

use Illuminate\Http\Request;
use App\Classes\CreateEntryHelper;
use App\Http\Resources\Entry as EntryResource;

class UpdateEntryHelper
{
    public $request,
           $record,//contains the record of the target entry
           $entry,
           $entry_time_before_save,//heep the entry time before updating the entry
           $create_entry_helper;// contains the target entry

    public function __construct($entry)
    {
        $this->request = request();
        $this->record = $entry->record;
        $this->entry = $entry;
        $this->create_entry_helper = new CreateEntryHelper($this->record);
        $this->entry_time_before_save = $entry->entry_time;
    }

    //check if the current entry is the last one of this record
    public function is_last_entry($current_entry = null)
    {
        if (!$current_entry) {
            $current_entry = $this->entry;
        }
        $last_entry = $this->create_entry_helper->get_task_last_entry();

        if ($last_entry->id == $current_entry->id) return true;
        return false;
    }

    //get the next entry of a task refering to the current
    public function get_next_entry()
    {
        $current_entry = $this->entry;
        $next_entry = $this->record->entries
                           ->first(function($entry) use($current_entry) {
                                return $current_entry->id < $entry->id;
                           });
        
        return $next_entry;
    }

    //get the previous entry of a task refering to the current
    public function get_previous_entry()
    {
        $current_entry = $this->entry;
        $previous_entry = $this->record->entries
                            ->last(function($entry) use($current_entry) {
                                return $current_entry->id > $entry->id;
                            });
        
        return $previous_entry;
    }

    //to check if the incomming entry time is between the previous entry time and the next one
    public function entry_time_in_middle()
    {
        $time_is_greater_checker = $this->entry_time_greater_prev();
        $time_is_less_checker = $this->entry_time_less_next();

        //check if incomming entry time is less than next entry time
        if (!$time_is_greater_checker->success) {
            
            return $time_is_greater_checker;
        }
        //check if incomming entry time is greater than previous entry time
        else if (!$time_is_less_checker->success) {
            return $time_is_less_checker;            
        }
        
        return to_object(['success' => true]);
    }

    //check if incomming entry time is greater than the previous entry time
    public function entry_time_greater_prev()
    {
        $previous_entry = $this->get_previous_entry();

        if (!date_greater_than($this->request->entry_time, $previous_entry->entry_time)) {
            return to_object([
                'success' => false,
                'message' => "Please the entry time must be greater than the one of the previous entry".
                            "({$previous_entry->entry_time})"
              ]);            
        }

        return to_object(['success' => true]);
    }

    //check if incomming entry time is less than the next entry time   
    public function entry_time_less_next()
    {
        //if the current entry is the last entry of record then approve
        if ($this->is_last_entry()) return to_object(['success' => true]);

        $next_entry = $this->get_next_entry();

        if (!date_greater_than($next_entry->entry_time, $this->request->entry_time)) {
            
            return to_object([
                    'success' => false,
                    'message' => "Please the entry time must be less than the one of the next entry".
                                "({$next_entry->entry_time})"
                  ]);
        }
        
        return to_object(['success' => true]);
    }

    /*
     - Brain function
     - method to be called for giving the response of entry update
     - will call the processor functions according to the entry type
    */
    public function response()
    {
        $current_entry_type = $this->entry->entry_type;

        if ($current_entry_type == 'start') return $this->edit_start();

        if ($current_entry_type == 'pause') return $this->edit_pause();

        if ($current_entry_type == 'resume') return $this->edit_resume();

        if ($current_entry_type == 'end') return $this->edit_end();
    }

    //when want to update the start entry
    public function edit_start()
    {
        //we check if is the last entry to update it directly
        if ($this->is_last_entry()) {
            
            $this->entry->entry_time = $this->request->entry_time;
            $this->entry->save(); 
            return $this->build_response(); 
        }
       
        /*
        update the current entry only if the incomming entry time is less than the one of 
        the next entry
        */
        $update = $this->update_on_date_less();

        if (! $update->success) {
            return response([
                'success' => false,
                'message' => $update->message
            ],400);
        }

        return $this->build_response();
         
    }

    /*
     - update the current entry only if the incomming entry time is less than the next enty time
     - Then calculate and update the duration of the next entry refering to the current entry time
     - applied only on start and resume entry type
    */
    public function update_on_date_less()
    {
            
        //check if the incomming entry time is less than the next entry time
        $time_is_less_checker = $this->entry_time_less_next();

        if (! $time_is_less_checker->success) {
            return $time_is_less_checker;
        }
        
        //update the current entry time
        $this->entry->entry_time = $this->request->entry_time;
        $this->entry->save();

        //update the duration of the next entry
        $next_entry = $this->get_next_entry();
        $next_entry->entry_duration = diffSecond($this->entry->entry_time,$next_entry->entry_time);
        $next_entry->save();

        return to_object(['success' => true]);
    }

    //when want to update the pause entry
    public function edit_pause()
    {

        //check if the incomming entry time is between previous and next entry time
        $time_is_middle = $this->entry_time_in_middle();

        if (! $time_is_middle->success) {
            
            return response([
                'success' => false,
                'message' => $time_is_middle->message
            ],400);
        }
        
        $previous_entry = $this->get_previous_entry();

        // calculate the duration then save
        $this->entry->entry_time = $this->request->entry_time;
        $this->entry->entry_duration = diffSecond($previous_entry->entry_time,$this->entry->entry_time);
        $this->entry->save();

        return $this->build_response();
    }

    //when want to update the resume entry
    public function edit_resume()
    {

        //check if the incomming entry time is between previous and next entry time
        $time_is_middle = $this->entry_time_in_middle();

        if (! $time_is_middle->success) {
            
            return response([
                'success' => false,
                'message' => $time_is_middle->message
            ],400);
        }

        //update the current entry time directly if it's a last entry
        if ($this->is_last_entry()) {
            $this->entry->entry_time = $this->request->entry_time;
            $this->entry->save();
            return $this->build_response();
        }
        
        //update the current entry time
        $this->entry->entry_time = $this->request->entry_time;
        $this->entry->save();
        //update the duration of the next entry
        $next_entry = $this->get_next_entry();
        $next_entry->entry_duration = diffSecond($this->entry->entry_time,$next_entry->entry_time);
        $next_entry->save();

        return $this->build_response();

    }

    //when want to update the end entry
    public function edit_end()
    {
        //check if incomming entry time is greater than the previous entry time
        $time_is_greater_checker = $this->entry_time_greater_prev();

        if (!$time_is_greater_checker->success) {
            
            return response([
                'success' => false,
                'message' => $time_is_greater_checker->message
            ],400);
        }

        //update entry time only if previous entry is pause
        $previous_entry = $this->get_previous_entry();

        if ($previous_entry->entry_type  == 'pause') {
            $this->entry->entry_time = $this->request->entry_time;
            $this->entry->save();
            return $this->build_response();
        }

        $this->entry->entry_time = $this->request->entry_time;
        $this->entry->entry_duration = diffSecond($previous_entry->entry_time,$this->entry->entry_time);
        $this->entry->save();

        return $this->build_response();

    }

    //save the task history
    public function save_history()
    {
        $previous_time = $this->entry_time_before_save;
        $history_description = "Updated the time of the ".
                               entry_index($this->record->entries,$this->entry->id).
                               "({$this->entry->entry_type} entry) entry task from ".
                               "{$previous_time} to: ".$this->entry->entry_time;

        record($this->record)->track_action_with_description('update_entry', $history_description);
    }

    //this will save the task history then build the update response when success
    public function build_response()
    {
        //log the task hisstory
        $this->save_history();

        return response([
            'success' => true,
            'message' => 'The entry is successfully updated',
            'entry' => new EntryResource($this->entry)
        ]);
    }
   

}