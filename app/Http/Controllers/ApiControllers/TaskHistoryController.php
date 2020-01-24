<?php

namespace App\Http\Controllers\ApiControllers;

use App\Record;
use App\TaskHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskHistoryCollection;
use App\Http\Resources\TaskHistory as TaskHistoryResource;

class TaskHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $task_histories = TaskHistory::all();
        return new TaskHistoryCollection($task_histories);
    }

    public function show(TaskHistory $taskHistory)
    {
        return response([
            'success' => true,
            'task_history' => new TaskHistoryResource($taskHistory)
        ]);
    }

    //getting task histories of a specific task(record)
    public function record_histories($record_id)
    {
        $record = Record::find($record_id);

        if (! $record) {
            return response([
                'success' => false,
                'message' => 'Trying to get task histories of a task which does not exist'
            ],404);
        }
        
        return new TaskHistoryCollection($record->task_histories);
    }

 
}
