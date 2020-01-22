<?php
namespace App\Classes;

class UpdateEntryHelper
{
    public $request,
           $record;

    public function __construct($record)
    {
        $this->request = request();
        $this->record = $record;
    }

    
}