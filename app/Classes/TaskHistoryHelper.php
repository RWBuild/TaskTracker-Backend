<?php
namespace App\Classes;


class TaskHistoryHelper
{
    private 
        $history_description = null,// description of the history to create: null by default
        $history_time, // time at which the history will be saved
        $history_type,//this will contain the action like: create_task
        $record, // the record to which the history belongs
        $known_actions = [ // predefined actions(description)
            'create_task',
            'start_task',
            'pause_task',
            'resume_task',
            'end_task',
            'update_entry',
            'delete_entry',
            'delete_task',
            'update_task', 
        ];

    public function __construct()
    {
        //set hidtory time to current
        $this->setTime();
    }

    //function to set the history description 
    public function setRecord($record)
    {
        $this->record = $record;
    }
    
    //function to set the history description 
    public function setDescription($description)
    {
        $this->history_description = $description;
    }

    //function to set the history time
    public function setTime()
    {
        $this->history_time = app_now();
    }

    //function to set the history type
    public function setType($history_type)
    {
        $this->history_type = $history_type;
    }
    
    public function save()
    {
        $task_history = $this->record
                              ->task_histories()
                              ->create([
                                'history_time' => $this->history_time,
                                'history_type' => $this->history_type,
                                'description' => $this->history_description,
                            ]);
    }
    
    // check if the action is valid then set it as the history type if yes
    public function action_is_valid($action)
    {
         //if action not valid, the sytem throw an exception
        if (! in_array($action, $this->known_actions)) {
            trigger_exception("Trying to register an unknown action for task history. ". 
                              "Received action [{$action}] while expect [".implode(',',$this->known_actions)."]");            
        }
        $this->setType($action);
        
    }

    //this will set receive the task on which the history will be saved
    public function get_record($record){
        //if record doesn't exist the sytem throw an exception
        if (!$record) {
            trigger_exception("Trying to register a task history of undefined task,please provide an existing task");                
        }
        $this->setRecord($record);
        return $this;
    }

    //function to be called outside when want to register a task action without description
    public function track_action($action)
    {
        //check if the passed action is valid
        $this->action_is_valid($action);

        //saving the history
        $this->save();
        
    }
    
    //function to be called outside when want to register a task action with descripoion
    public function track_action_with_description($action,$description=null)
    {
        //check if the passed action is valid
        $this->action_is_valid($action);
        $this->setDescription($description);
        //saving the history
        $this->save();
        
    }
}