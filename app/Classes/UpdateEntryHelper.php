<?php
namespace App\Classes;

class UpdateEntryHelper
{
    public $request;

    public function _construct()
    {
        $this->request = request();
    }
   
    public function check_if_record_has_many_entries()
    {
       
    }
}