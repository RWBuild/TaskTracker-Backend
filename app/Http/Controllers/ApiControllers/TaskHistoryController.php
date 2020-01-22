<?php

namespace App\Http\Controllers\ApiControllers;

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
        return new TaskHistoryResource($taskHistory);
    }

    //getting task histories of a specific task(record)
    public function record_histories(Record $record)
    {
        $task_histories = $record->task_histories;
        return new TaskHistoryCollection($task_histories);
    }

 
}
