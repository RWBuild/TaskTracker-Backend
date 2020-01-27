<?php
namespace App\Classes;

use Illuminate\Http\Request;
use App\Classes\UpdateEntryHelper;

class DeleteEntryHelper
{
    public $request,
           $entry,//incoming entry
           $record,//record of incoming entry
           $entry_type,//keep entry type to be used after deleting the entry
           $update_entry_helper;//helper of the entry

    public function __construct($entry)
    {
        $this->request = request();
        $this->entry = $entry;
        $this->record = $entry->record;
        $this->entry_type = $entry->entry_type;
        $this->update_entry_helper = new UpdateEntryHelper($entry);
    }
    
    //reload record entries 
    public function load_record()
    {
        return $this->record->load('entries');
    }

    public function get_last_entry()
    {
        return $this->load_record()->entries->last();
    }

    //check if the user is allowed to delete this entry
    public function user_is_allowed()
    {
        //check if the user is the owner of the entry
        if(!isOwner($this->record))
        {
            return to_object([
                'success' => false,
                'message' => "you are not the owner of this entry"
            ]);
        }
       
        //check if the entry is the last of the record
        $last_entry_checker = $this->last_entry_checker();

        if (! $last_entry_checker->success) {
           return $last_entry_checker;
        }


        return to_object(['success' => true]);
    }

    public function record_is_current()
    {
        return (Bool) $this->record->is_current;
    }

    /*
      - check if the entry is a last of record to delete it
      - otherwise prevent the operation
    */

    public function last_entry_checker()
    {
        if (! $this->update_entry_helper->is_last_entry()) {
           return to_object([
               'success' => false,
               'message' => 'You can delete only the last entry'
           ]); 
        }

        return to_object(['success' => true]);
    }

    /*
     - Brain of delete entry helper
     - method to be called for giving the response of entry delete
     - will call the processor functions according to the entry type
    */
    public function response()
    {
        //check if the user is allowed to perform this operation
        $is_allowed_checker = $this->user_is_allowed();

        if (! $is_allowed_checker->success) {
            return $this->build_error($is_allowed_checker);
        }
        
        $current_entry_type = $this->entry->entry_type;

        if ($current_entry_type == 'start') return $this->delete_start();

        if ($current_entry_type == 'pause') return $this->delete_pause();

        if ($current_entry_type == 'resume') return $this->delete_resume();

        if ($current_entry_type == 'end') return $this->delete_end();        
    }

   //processor for deleting a start entry
    public function delete_start()
    {
        //delete the entry
        $this->entry->delete();

        //update the entry record status
        $this->record->is_current = false;
        $this->record->status = 'pending';
        $this->record->save();

        //call the response
        return $this->build_response();

    }

    //processor for deleting a pause entry
    public function delete_pause()
    {
        /*
         if the record of this entry is not 
         current  pause the current user record 
         */
        if (! $this->record->is_current) {
            $this->update_entry_helper->create_entry_helper
                                  ->pause_current_user_task();
        }

        //delete the current pause entry
        $this->entry->delete();

        
        //get last entry after delete
        $last_entry = $this->get_last_entry();

        //change the status of the entry record depending on its last entry
        $this->record->is_current = true;
        $this->record->status = $last_entry->entry_type;
        $this->record->save();
        
        //call the response
        return $this->build_response();

    }

    public function delete_resume()
    {
        //delete the resume entry
        $this->entry->delete();

        //get last entry after delete
        $last_entry = $this->get_last_entry();

        //change the status of the entry record depending on its last entry
        $this->record->status = $last_entry->entry_type;
        $this->record->save();

        //call the response
        return $this->build_response();
    }

    public function delete_end()
    {
        
        //get last entry before delete
        $previous_entry = $this->update_entry_helper
                               ->get_previous_entry();

        //turn other record to current false if last entry type is not pause
        if ($previous_entry->entry_type != 'pause') {
            //check if the entry record is not the current one
            if (! $this->record->is_current) {
                $this->update_entry_helper->create_entry_helper
                                  ->pause_current_user_task();
            }
        }

        //delete entry
        $this->entry->delete();
        
        //change the status of the entry record depending on its last entry
        $this->record->status = $previous_entry->entry_type;
        $this->record->is_current = $previous_entry->entry_type == 'pause'? false : true;
        $this->record->is_opened = true;
        $this->record->is_finished = false;
        $this->record->save();
        
        //call the response
        return $this->build_response();
    }

    //save the task history
    public function save_history()
    {
        $history_description = "Deleted a {$this->entry_type} entry";

        record($this->record)->track_action_with_description('delete_entry', $history_description);
    }

    //this will save the task history then build the delete response when success
    public function build_response()
    {
        //log the task hisstory
        $this->save_history();

        return response([
            'success' => true,
            'message' => 'The entry is successfully deleted',
        ]);
    }

    //this will build response for error response
    public function build_error($error)
    {
        //return error status 400 in case no other status provided
        $error_status =  !isset($error->status) ? 400 : $error->status; 
        return response([
            'success' => false,
            'message' => $error->message
        ], $error_status);
    }
    

    
}