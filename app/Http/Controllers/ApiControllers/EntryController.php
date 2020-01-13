<?php

namespace App\Http\Controllers\ApiControllers;

use App\Entry;
use Carbon\Carbon;
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
    public function store(Request $request)
    {
        $this->validate($request,[
            'record_id'=>'integer|required',
            'entry_type'=>'string|required',
        ]);

        if($request->entry_type == 'start')
        {
            $entry = Entry::create([
                'record_id' => $request->record_id,
                'entry_type' => $request->entry_type,
            ]);
            return response()->json([
                'success' => true,
                'entry' => new EntryResource($entry),
            ]);
        }

        else if($request->entry_type == 'pause')
        {
            $entry = new Entry([
                'record_id' => $request->record_id,
                'entry_type' => $request->entry_type,
            ]);
            $entry->save();
            $id = $entry->id;
            $id2 = $id - 1;
            $current_time = new Carbon(Entry::find($id)->entry_time); 
            $previous_time = new Carbon(Entry::find($id2)->entry_time);
            $duration = $current_time->diff($previous_time)->format('%H:%I:%S');
            $entry->update([
                'entry_duration' => $duration,
            ]);
            return response()->json([
                'success' => true,
                'entry' => new EntryResource($entry),
            ]);


        }
        else if($request->entry_type == 'resume')
        {
            $entry = Entry::create([
                'record_id' => $request->record_id,
                'entry_type' => $request->entry_type,
            ]);
            return response()->json([
                'success' => true,
                'entry' => new EntryResource($entry),
            ]);
        }

        else if($request->entry_type == 'end')
        {
            $entry = new Entry([
                'record_id' => $request->record_id,
                'entry_type' => $request->entry_type,
            ]);
            $entry->save();
            $id = $entry->id;
            $id2 = $id - 1;
            $current_time = new Carbon(Entry::find($id)->entry_time); 
            $previous_time = new Carbon(Entry::find($id2)->entry_time);
            $duration = $current_time->diff($previous_time)->format('%H:%I:%S');
            $entry->update([
                'entry_duration' => $duration,
            ]);
            return response()->json([
                'success' => true,
                'entry' => new EntryResource($entry),
            ]);
        }

        else
        {
            return 'invalid request';
        }

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
    public function update(Request $request, Entry $entry)
    {
        $this->validate($request,[
            'record_id',
            'entry_type',
            'entry_time',
            'entry_duration'
        ]);
        
        $entry = update($request->all());
        $entry = Entry::find($request->id);
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
    public function destroy(Entry $entry)
    {
        $entry->delete($entry);
    }
}
