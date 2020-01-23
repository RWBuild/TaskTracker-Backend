<?php
namespace App\Classes;

use Illuminate\Http\Request;
use App\Classes\CreateEntryHelper;

class UpdateEntryHelper
{
    public $request,
           $record,//contains the record of the target entry
           $entry,
           $create_entry_helper;// contains the target entry

    public function __construct($entry)
    {
        $this->request = request();
        $this->entry = $entry;
        $this->record = $entry->record;
        $this->create_entry_helper = new CreateEntryHelper($this->record);
    }

    //check if the current entry is the last one of this record
    public function is_last_entry($current_entry)
    {
        $last_entry = $this->create_entry_helper->get_task_last_entry();

        if ($last_entry == $current_entry) return true;
        return false;
    }

    //get the next entry of a task refering to the current
    public function get_next_entry()
    {
        $entries_after_current = $this->record->entries
                                            ->first(function($entry){

                                            });
        $next_entry = $entries_after_current->shif();
        
        return $next_entry;
    }

    //get the previous entry of a task refering to the current
    public function get_previous_entry()
    {
        $entries_before_current = $this->record->entries()
                           ->where('id','<',$this->entry->id)
                           ->get();
        $next_entry = $entries_after_current->pop();
        
        return $next_entry;
    }

    //when want to update the start entry
    public function edit_start()
    {

    }

    //when want to update the pause entry
    public function edit_pause()
    {

    }

    //when want to update the resume entry
    public function edit_resume()
    {

    }

    //when want to update the end entry
    public function edit_end()
    {

    }
   

}