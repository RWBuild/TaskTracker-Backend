<?php
namespace App\Classes;

use Illuminate\Http\Request;
use App\Classes\CreateEntryHelper;

class DeleteEntryHelper
{
    public $request,
           $entry;

    public function __construct($entry)
    {
        $this->request = request();
        $this->entry = $entry;
    }

    //get the record id of an entry
    public function get_record()
    {
        $record = $this->entry->record;
        return $record;
    }
 
    //get current record
    public function current_record()
    {
        return $record = $this->get_record();
        
    }

    //get current entry type
    public function get_entry_type()
    {
        return $this->entry->entry_type;
    }
    
    public function delete_entry()
    {
        $entry_type = $this->get_entry_type();

        if($entry_type == 'start')
        {
           $record = $this->current_record_status();
           $record->status = "pending";
           $record->is_current = false;
           $record->save(); 
        }

        if($entry_type == 'pause')
        {
            $record = $this->current_record();
            $create_entry_helper = new CreateEntryHelper($record);
            $create_entry_helper->pause_current_user_task();
        }

        if($entry_type == 'resume')
        {
            
        }

        if($entry_type == 'end')
        {
            
        }
    }
    
}