<?php
namespace App\Classes;

use Illuminate\Http\Request;
use App\Http\Resources\Record as RecordResource;
use App\Classes\Parents\EntryHelper;
use App\Http\Resources\Entry as EntryResource;

/**
 * This helper class will delete the current entries of
 * given Task(record) if these entries are well ordered
 * depending on the system expectation
 * eg: START,PAUSE,RESUME,END
 * otherwhise it will throw an error
 */
class DeleteEntryHelper extends EntryHelper
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
        $this->user_is_allowed();

        // cast array to laravel collection
        $this->entries = toCollection($this->request->entries);
    }

    //check if the user is allowed to delete this entry
    public function user_is_allowed()
    {
        //check if the record exist
        if (! $this->record) {
            $this->build_error('These record does not exist or it may not belong to you',404);
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
        $new_entries = $this->saveBundleEntries();

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
            $this->build_error("The first entry should be a start entry Buts not $first_entry->entry_type");
        }
    }
    /**
     * this will oblige a user to pause  or to end a task
     * when the task is not current
     * the method will be called only when user is trying to save 
     * entries without pause or end as last entry type
     * This will be used only when the task is not current
     */
    public function obligePauseWhenNotCurrent()
    {
        $last_entry = $this->getLastEntry();

        //process only when the task is not current
        if (!$this->is_current()) {
        
            if (!in_array($last_entry->entry_type,['pause','end'])) {
                $this->build_error('Please the last entry type of this task must be: pause or end');
            }
        }
    }

    /**
     * Get the first entry of the given entries
     * @param Collection list $entries
     * @return Object 
     */
    public function getFirstEntry($entries = null)
    {
        if (!$entries) {
            $entries = $this->entries;
        }

        return $entries->first();
    }

    /**
     * Get the last entry of the given entries
     * @param Collection list $entries
     * @return Object 
     */
    public function getLastEntry($entries = null)
    {
        if (!$entries) {
            $entries = $this->entries;
        }
        
        return $entries->last();
    }

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

    public function buildResponse($data)
    {
        //log task history
        record($this->record)->track_action("delete_entries");

        return response()->json([
            'success' => true,
            'message' => "The last version of the task entries is well saved",
            'record' => new RecordResource($this->record)
        ]);
    }


    
}