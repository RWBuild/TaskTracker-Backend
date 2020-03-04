<?php
namespace App\Classes;

use Illuminate\Http\Request;
use App\Classes\Parents\BundleEntryHelper;
use App\Http\Resources\Record as RecordResource;

/**
 * This helper class will delete the current entries of
 * given Task(record) if these entries are well ordered
 * depending on the system expectation
 * eg: START,PAUSE,RESUME,END
 * otherwhise it will throw an error
 */
class SaveBundleEntryHelper extends BundleEntryHelper
{
    /** 
     * will keep the incomming entries
     */
    public $entries = [];

    public function __construct($record)
    {
        $this->request = request();
        $this->record = $record;

        //check if the user is allowed to perform this operation
        $this->userIsAllowed();

        // cast array to laravel collection
        $this->entries = toCollection($this->request->entries);
    }

    //check if the user is allowed to delete this entry
    public function userIsAllowed()
    {
        //when user didn't check in
        if (!user()->has_checked) {
            $this->build_error('Please check in first');
        }
        //check if the record exist
        if (! $this->record) {
            $this->build_error('This record does not exist',404);
        }

        //check if the user is the owner of the entry
        if(!isOwner($this->record)) {
           $this->build_error('you are not the owner of this task');
        }

        //check if the incomming entries type is Array
        if (!is_array($this->request->entries)) {
            $this->build_error('Please the entries must be an Array');
        }
    }

    /**
     * This will validate first all entries
     * then call the function to delete the task entries
     * and after call the function to recreate the new task entries
     */
    public function response () 
    {
        //if entries is empty,it means user want to delete all task entries
        if ($this->entries->isEmpty()) return $this->deleteAll();

        //General validation of incomming entries
        $this->validateEntries();

        //update the entries of the task
        $new_entries = $this->saveBundl;eEntries();

        //change task status depending on the last entry
        $this->changeRecordStatus($new_entries);

        return $this->buildResponse($new_entries);
    }

    /**
     * In case the array of entrues isempty then
     * Delete all task entries
     */
    public function deleteAll()
    {
        $this->record->entries()->delete();
        $this->record->status = 'pending';
        $this->record->is_opened = 'false';
        $this->is_finished = false;
        $this->is_current = false;

        return $this->buildResponse();
    }

    /**
     * This will validate the incomming array of objects of entries 
     * By checking if they respect the order of the entry types
     * @example : check if they respect: START,PAUSE,RESUME and END
     */
    public function validateEntries() 
    {
        //verify if first entry is a START entry
        $this->firstItemIsStartEntry();

        //Detect the right order of entries
        $this->detectRightEntryOrder();

        //check if last entry is pause or end when the record is not current
        $this->obligePauseWhenNotCurrent();
    }

    /**
     * check if the first object of the incomming entries is 
     * A start entry other wise throw an error
     */
    public function firstItemIsStartEntry()
    {
        $first_entry = $this->getFirstEntry();
        if ($first_entry->entry_type != 'start') {
            $this->build_error("The first entry should be a start entry But not $first_entry->entry_type");
        }
    }
    /**
     * this will oblige a user to pause  or to end a task
     * when the task is not current and when the user has another
     * current task
     * the method will be called only when user is trying to save 
     * entries without pause or end as last entry type
     * This will be used only when the task is not current
     */
    public function obligePauseWhenNotCurrent()
    {
        $last_entry = $this->getLastEntry();

        //process only when the task is not current and user has another current task
        if (!$this->is_current()) {
        
            if (!in_array($last_entry->entry_type,['pause','end'])) {
                $this->build_error('Please the last entry type of this task must be: pause or end');
            }
        }
    }

    /**
     * 
     */
    public function changeRecordStatus($new_entries)
    {
        $last_entry = $this->getLastEntry($new_entries);
        
        //set record as finished when last entry is end
        if ($last_entry->entry_type == 'end') {
            $this->record->status = 'end';
            $this->record->is_current = false;
            $this->record->is_opened = false;
            $this->record->is_finished = true;

        } else { // don't touch on is_current
            $this->record->status = $last_entry->entry_type;
            $this->record->is_finished = false;
            $this->record->is_opened = true;
        }

        $this->record->save();
    }
    
}