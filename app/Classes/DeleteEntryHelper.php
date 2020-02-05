<?php
namespace App\Classes;

use Illuminate\Http\Request;
use App\Classes\UpdateEntryHelper;
use App\Classes\Parents\EntryHelper;

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

    public function buildResponse()
    {
        //log task history
        record($this->record)->track_action("delete_entries");

        return response()->json([
            'success' => true,
            'message' => "The new updates of the task are well saved"
        ]);
    }


    
}