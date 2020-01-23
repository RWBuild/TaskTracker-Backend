<?php
namespace App\Classes;

use Illuminate\Http\Request;

class UpdateEntryHelper
{
    public $request,
           $entry;

    public function __construct($entry)
    {
        $this->request = request();
        $this->entry = $entry;
    }
   
    //get record id of an entry
    public function get_record_id()
    {
       return $this->entry->record->id;
    }

    public function check_record_id()
    {
        $entry_record_id = $this->get_record_id();
        return $entry_record_id;
    }
}