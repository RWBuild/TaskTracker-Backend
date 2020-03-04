<?php
namespace App\Classes\Parents;

use Carbon\Carbon;
use App\Http\Resources\Entry as EntryResource;
use App\Http\Resources\Record as RecordResource;

class BundleEntryHelper 
{
    /**
     * To keep the current request
     * @var Request
     */
    public $request;

    /**
     * To keep the current record
     * @var Record
     */
    public $record;

    /**
     * To keep the current entry: 
     * used for update and delete entry
     * @var Entry
     */
    public $entry;

    /**
     * The entry types known by the application
     * @var Array
     */
    public $knownEntryType = [
        'start','pause','resume','end'
    ];

    /** 
     * To save the current iteration when loop
     * through an array
     */
    public $currentIteration = null;

    /**
     * will keep the last entry of the current record
     * @var Entry
     */
    public $lastEntry;

    /**
     * will keep the previous entry based on the 
     * current one
     * @var Entry
     */
    public $previousEntry;

    /**
     * will keep the next entry based on the 
     * current one
     * @var Entry
     */
    public $nextEntry;

    /** 
     * to check if a record of the entry in proccess
     * is current
     * @return bool
    */
    public function is_current ()
    {  
        return $this->record->is_current;
    }

    /** 
     * To check if the incomming time is not future time
     * for preventing user to create or update an entry with 
     * a future time
     * @return Object 
    */
    public function timeFutureChecker()
    {
        $now = app_now();
        if (date_greater_than($this->entry->entry_time,$now)) {
            $this->build_error([
                'message' => 'Please the last entry time can not be a future time'
            ]);
        }
    }

    /** 
     * get the next entry of a task based on the current entry
     * @return Entry
    */
    public function getNextEntry($entries = null)
    {
        if (!$entries) {
            $entries = $this->entries;
        }

        $next_entry = $entries[$this->currentIteration + 1];
        $this->nextEntry = $next_entry;

        return $next_entry;
    }

    /** 
     * get the previous entry based on the current entry
     * @return Entry
    */
    public function getPreviousEntry($entries = null)
    {
        if (!$entries) {
            $entries = $this->entries;
        } 

        $previous_entry = $entries[$this->currentIteration - 1];
        $this->previousEntry = $previous_entry;

        return $previous_entry;
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

    /**
     * Check if entry is a first entry of the task
     */
    public function isFirstEntry()
    {
        return $this->currentIteration == 0;
    }

    /**
     * Check if entry is a last entry of the task
     */
    public function isLastEntry()
    {
        return $this->currentIteration == count($this->entries) - 1;
    }

    /**
     * Check if entry is in middle of entries of the task
     */
    public function isMiddleEntry()
    {
        return !$this->isFirstEntry() and !$this->isLastEntry();
    }

    /**
     * This will check if entries are ordered in the right
     * way that the system expect them to be
     */
    public function detectRightEntryOrder()
    {
        //looping through all incomming entries
        $this->entries = $this->entries->map(function($entry, $index) {
                //set the current entry(in loop) globaly into this class
                $this->entry = $entry;
                //set the iteration globaly
                $this->currentIteration = $index;

                //validate the entries time
                $this->entryTimeChecker();

                //detect next entry type if current entry(in loop) is not a last entry
                if (!$this->isLastEntry()) {
                    $this->nextEntryTypeChecker();
                }

                //recalculate the entry duration when type is pause and end only otherwise set to null
                return $this->calculateEntryDuration();
            });
    }

    /**
     * This will check if entries time are following the 
     * expected rules
     * *****************************************************
     * 1. check if first entry time is not less than 
     * the creation time of the task and check if the first 
     * time has less time than the next entry time
     * *****************************************************
     * 2. check if the entry in middle has time between 
     * the previous and next entry
     * * *****************************************************
     * 3. check if the last entry time is not a future
     * time and greater than the previous entry time
     */
    public function entryTimeChecker()
    {
        $current_entry = $this->entry;
        $entry_type = $current_entry->entry_type;
        
        //check if entry time is a valid date time
        $this->isValidDateTime($current_entry);

        //check time when entry is a first entry
        $this->checkTimeWhenFirst($current_entry);

        //check time when entry is in middle
        $this->checkTimeWhenInMiddle($current_entry);

        //check time when entry is the last one
        $this->checkTimeWhenLast($current_entry);
        
    }

    /**
     * check if the the given entry time is valid
     */
    public function isValidDateTime($current_entry)
    {
        //check if is a datetime
        //dump(isDateTime($current_entry->entry_time));
        if (!isDateTime($current_entry->entry_time)) {
            $this->build_error("The time of the ". entry_index($this->currentIteration)
                               ." entry of this task is invalid ({$current_entry->entry_time})");
        }
    }

    /**
     * This will validate only the first entry
     * * *****************************************************
     * 1. validate if the first entry time is greater than
     * the creation time of task
     * * *****************************************************
     * 2. check if the first entry time is less than the time
     * of the next entry time
     */
    public function checkTimeWhenFirst($current_entry)
    {
        //if not first return void
        if (!$this->isFirstEntry()) return ;

        //When current entry time is less than the creation time of the task 
        if (!date_greater_than($current_entry->entry_time, $this->record->created_at)) {
            $this->build_error("The START entry time should be greater than ".
                               " the creation time of the task({$this->record->created_at})");
        }

        //check the current time based on the next entry time

        //if is last entry return void
        if ($this->isLastEntry()) return ;

        //when current entry time is greater than the next entry time
        $next_entry = $this->getNextEntry();

        if (date_greater_than($current_entry->entry_time, $next_entry->entry_time)) {
            $this->build_error("The START entry time should be less than ".
                               ": {$next_entry->entry_time}(see next entry)");
        }
    }

    /**
     * This will validate only the entries in middle
     * All PAUSES and RESUMES
     * * *****************************************************
     * check if the last entry time is not a future
     * time and not eqaul to the previous time
     */
    public function checkTimeWhenInMiddle($current_entry)
    {
        //if not in middle return void
        if (!$this->isMiddleEntry()) return ;

        //when previous entry time is greater that current entry time
        $previous_entry = $this->getPreviousEntry();

        if (!date_greater_than($current_entry->entry_time,$previous_entry->entry_time)) {
            $this->build_error("The time of the ".entry_index($this->currentIteration).
                " entry  should be greater than :{$previous_entry->entry_time}(see previous entry)");
        }

        //when current entry time is greater than the next entry time
        $next_entry = $this->getNextEntry();

        if (date_greater_than($current_entry->entry_time, $next_entry->entry_time)) {
            $this->build_error("The time of the ".entry_index($this->currentIteration).
                                " entry  should be less than :{$next_entry->entry_time}(see next entry)");
        }

    }

    /**
     * This will validate only the Last entry
     * * *****************************************************
     * check if the last entry time is greater than the previous
     * or not a future time and not greater than the entry checkout time
     */
    public function checkTimeWhenLast($current_entry)
    {
        //if not last entry return void
        if (!$this->isLastEntry()) return ;

        //check only when entry type is not START
        if ($current_entry->entry_type != 'start') {
            //check if the last entry time is greater than the previous
            $previous_entry = $this->getPreviousEntry();
        
            if (!date_greater_than($current_entry->entry_time,$previous_entry->entry_time)) {
                $this->build_error("The time of the last entry should be greater than ".
                                 ":{$previous_entry->entry_time}(see previous entry)");
            } 
        }
        
        //if current entry time is a future time return error
        $this->timeFutureChecker();

        //check time less than checkout time
        $this->checkoutTimeChecker();
    }

    /**
     * This will check if the current entry time is less than 
     * its checkout time
     */
    public function checkoutTimeChecker()
    {
        $entry_date = (explode(' ', $this->entry->entry_time))[0];
        //get the checkout time of that entry
        $checkout_time = entry_checkout_time($entry_date);

        //when checkout time is null: don't process
        if (!$checkout_time) return;

        //when entry time is greater than checkout time
        if (date_greater_than($this->entry->entry_time,$checkout_time)) {
            $this->build_error("The last entry time must be less than checkout time".
                             "({$checkout_time})");
        } 

    }

    /**
     * This will detect the next entry type based on the current entry
     * @param Entry $current_entry
     * if next entry type is not right then throw an error
     */
    public function nextEntryTypeChecker($current_entry = null)
    {
        if (!$current_entry) {
            $current_entry = $this->entry;
        }
        
        //get next entry based on the current
        $next_entry = $this->getNextEntry();

        //check the type of the next entry
        $this->validateEntryType($next_entry, $this->currentIteration + 1); 
        
        //get list of types that may come after the current entry type 
        $get_right_next_types = $this->predictedNextEntry();

        //check if the current entry entry type is among the predicted ones
        if (!in_array($next_entry->entry_type ,$get_right_next_types )) {
           
           //a clear message of an error by providing where the order is bad
           //Eg: The next entry type after the 1st entry should be (PAUSE or END)
           $this->build_error("The next entry type after the ".entry_index($this->currentIteration)
                              ." entry should be ". arrayToString($get_right_next_types, ' or '));
        }
    }

    /**
     * This will check if a given entry has
     * the expected entry type : start,pause,resume
     * or end
     * otherwise throw an error
     * @param Object $entry
     * @param Int $entry_iteration
     * @Note: $entry_index is the index that $entry in the array
     * of entries
     */
    public function validateEntryType($entry, $entry_index = null)
    {
        if (!in_array($entry->entry_type, $this->knownEntryType)) {
            $this->build_error("The ". entry_index($entry_index)
                            ." entry of this task has an unexpected type({$entry->entry_type})");                   
        }
    }

    /**
     * This will predict the next entry type based on the current entry type
     * if the current entry is not the END
     */
    public function predictedNextEntry($entry = null)
    {
        if (!$entry) {
            $entry = $this->entry;
        }

        switch ($entry->entry_type) {
            case 'start':
                return ['pause','end'];
                break;

            case 'pause':
                return ['resume','end'];
                break;

            case 'resume':
                return ['pause','end'];
                break;

            case 'end': //throw an error in this case
                abort(500, 'Trying to predict the next entry type based on an END entry');
                break;
            default:
                abort(500, "Trying to predict the next entry type of unknown ".
                           "current entry type({$this->entry->entry_type})");
                break;
        }
    }

    /**
     * This will recalculate the entry duration
     * of pause and end entries of new task entries
     * that are comming from user 
     * this method will be called when user want to save
     * task entries after deleting or updating some entries
     * @return Object $entry
     */
    public function calculateEntryDuration($current_entry = null)
    {
        if (!$current_entry) {
            $current_entry = $this->entry;
        }

        //for start and resume duration is null
        if (in_array($current_entry->entry_type,['start','resume'])) {
            $current_entry->entry_duration = null;
            return $current_entry;
        }

        //get previous entry based on the current entry 
        $previous_entry = $this->getPreviousEntry();

        
        //set entry duration to null if the previous one is pause otherwise calculate
        //entry duration
        $current_entry->entry_duration = $previous_entry->entry_type != 'pause' ? 
                            diffSecond($previous_entry->entry_time,$current_entry->entry_time) : null;
        
        
        
        return $current_entry;
    }

    /**
     * this will delete the current task entries
     * then recreate task entries based on the new
     * ones 
     * @return Entry collection
     */
    public function saveBundleEntries()
    {
        
        //delete entries
        $this->record->entries()->delete();

        //cast the entries collection to array
        $entriesArray = collectionToArray($this->entries);
       
        //recreate task entries based on the new ones
        return $this->record->entries()->createMany($entriesArray);

    }
    
    /** 
     * this will throw an exception
     * @return throw
    */
    public function build_error($data,$status = null)
    { 
        if(is_string($data)) {
            trigger_exception($data, $status);
        } else {
            $data = to_object($data);
            trigger_exception($data->message, $status);
        }
        
    }

    /**
     * Log the task history then 
     * then return reponse
     * @return Response
     */
    public function buildResponse($data = null)
    {
        //log task history
        record($this->record)->track_action("save_entries");

        return response()->json([
            'success' => true,
            'message' => "The last version of the task entries is well saved",
            'record' => new RecordResource($this->record)
        ]);
    }
}
