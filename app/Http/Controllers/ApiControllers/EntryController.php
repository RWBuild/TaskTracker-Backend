<?php

namespace App\Http\Controllers\ApiControllers;

use DateTime;
use App\Entry;
use App\Record;
use Carbon\Carbon;
use App\Classes\CreateEntryHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\EntryCollection;
use App\Http\Resources\Entry as EntryResource;

class EntryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     
     //return the list of all entries
    public function index()
    {
        $entries = Entry::all();
        return new EntryCollection($entries);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     //storing an entry
    public function store(Request $request)
    {
        $user = user();
        $is_checked = $user->has_checked;
        $this->validate($request,[
            'record_id'=>'required|integer',
            'entry_type'=>'required|string',
            'entry_time'=>'required|date',
        ]);
        if($is_checked == 0)
        {
            return response()->json([
                'success' => false,
                'message' => 'the user must checkin first to create an entry',
            ]);
        }
        $record = user()->records()->find($request->record_id);

        $entry_helper = new CreateEntryHelper($record);

        //we check if record exist and avoid duplication of entry type
        $duplication_checker = $entry_helper->avoidEntryDuplication();
        if (!$duplication_checker->success) {
            return response([
                'success' => false,
                'message' => $duplication_checker->message
            ],$duplication_checker->status);
        }
   
        if ($request->entry_type == 'start') return $entry_helper->startTask();

        if ($request->entry_type == 'pause') return $entry_helper->pauseTask();

        if ($request->entry_type == 'resume') return $entry_helper->resumeTask();

        if ($request->entry_type == 'end') return $entry_helper->endTask();

    
}

    /**
     * Display the specified resource.
     *
     * @param  \App\Entry  $entry
     * @return \Illuminate\Http\Response
     */
    public function show(Entry $entry)
    {
        return new EntryResource($entry);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Entry  $entry
     * @return \Illuminate\Http\Response
     */
    public function edit(Entry $entry)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Entry  $entry
     * @return \Illuminate\Http\Response
     */

     //updating an entry
    public function update(Request $request, Entry $entry)
    {
        $this->validate($request,[
            'record_id'=>'integer|required',
            'entry_type'=>'string|required',
            'entry_time'=>'required',
            'entry_duration' => 'required',
        ]);
        
        $entry->update($request->all());

        //log task history
        $history_description = "Updated the entry time of the".
                               entry_index($entry->record->entries,$entry->id)." 
                               entry task to:".$request->entry_time;
        record($record)->track_action_with_description('update_entry',$history_description);
        return response([ 
            'status' => true,
            'message' => 'entry updated successfully',
            'entry' => new EntryResource($entry)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Entry  $entry
     * @return \Illuminate\Http\Response
     */

     //deleting an entry
    public function destroy(Entry $entry)
    {
        $record = $entry->record;
        
        if(!isOwner($record))
        {
            return response([
                'success' => false,
                'message' => "you are not the owner of this entry"
            ],403);
        }
        $last_entry_id = $entry->orderBy('id','desc')->first()->id;
        if($last_entry_id != $entry->id)
        {
            return response([
                'success' => false,
                'message' => 'the deleted entry must be a last entry'
            ],400);
        }

        $entry_type = $entry->entry_type;
        $entry->delete($entry);

        //log task history
        $history_description = "Deleted a task entry having a {$entry_type} type";
        record($record)->track_action_with_description('delete_entry',$history_description);

        return response( [
            'status' => true,
            'message' => 'entry deleted successfully',
        ],200);
    }
}
