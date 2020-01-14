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
        $this->validate($request,[
            'record_id'=>'integer|required',
            'entry_type'=>'string|required',
            'entry_time'=>'required',
        ]);

         //checking a type of an entry(start,pause,resume and end)
        if($request->entry_type == 'start')
        {
            $record = user()->records()->find($request->record_id);
            if(!$record)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'record does not exist',
                ]);
            }
            $entry = $record->entries()->orderBy('id','desc')->first();
            if(!$entry)
            {
                $entry = new Entry([
                    'record_id' => $request->record_id,
                    'entry_type' => $request->entry_type,
                    'entry_time' => $request->entry_time
                ]);
                $entry->save();

                return response()->json([
                    'success' => true,
                    'entry' => new EntryResource($entry),
                ]);
            }
            $entry->entry_type;
            if($entry->entry_type == $request->entry_type)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'you have already started',
                ]);
            }
            else
            {
                $entry = new Entry([
                    'record_id' => $request->record_id,
                    'entry_type' => $request->entry_type,
                    'entry_time' => $request->entry_time
                ]);
            $entry->save();

                return response()->json([
                    'success' => true,
                    'entry' => new EntryResource($entry),
                ]);
            }
        }

        else if($request->entry_type == 'pause')
        {
            $record = user()->records()->find($request->record_id);
            if(!$record)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'record does not exist',
                ]);
            }
            $entry = $record->entries()->orderBy('id','desc')->first();
            $entry->entry_type;
            if($entry->entry_type == $request->entry_type)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'you have already paused',
                ]);
            }
            else
            {
                $new_entry = new Entry([
                    'record_id' => $request->record_id,
                    'entry_type' => $request->entry_type,
                    'entry_time' => $request->entry_time
                ]);
                $new_entry->save();
                $previous_time = ($entry->entry_time);
                $current_time = ($new_entry->entry_time);
                $duration = diffTime($previous_time,$current_time,'%H:%I:%S');
               
                $new_entry->update([
                'entry_duration' => $duration,
                ]);
                
                return response()->json([
                'success' => true,
                'entry' => new EntryResource($new_entry),
                ]);
            }
        }
        else if($request->entry_type == 'resume')
        {
            $record = user()->records()->find($request->record_id);
            if(!$record)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'record does not exist',
                ]);
            }
            $entry = $record->entries()->orderBy('id','desc')->first();
            $entry->entry_type;
            if($entry->entry_type == $request->entry_type)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'you have already resumed',
                ]);
            }
            else{
            $entry = new Entry([
                'record_id' => $request->record_id,
                'entry_type' => $request->entry_type,
                'entry_time' => $request->entry_time
            ]);
            $entry->save();

            return response()->json([
                'success' => true,
                'entry' => new EntryResource($entry),
            ]);
            }
        }
        else if($request->entry_type == 'end')
        {
            $record = user()->records()->find($request->record_id);
            if(!$record)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'record does not exist',
                ]);
            }
            $entry = $record->entries()->orderBy('id','desc')->first();
            $entry->entry_type;
            if($entry->entry_type == $request->entry_type)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'you have already ended',
                ]);
            }
            else
            {
                $new_entry = new Entry([
                    'record_id' => $request->record_id,
                    'entry_type' => $request->entry_type,
                    'entry_time' => $request->entry_time
                ]);
                $new_entry->save();
                $previous_time = ($entry->entry_time);
                $current_time = ($new_entry->entry_time);
                $duration = diffTime($previous_time,$current_time,'%H:%I:%S');
               
                $new_entry->update([
                'entry_duration' => $duration,
                ]);
                
                return response()->json([
                'success' => true,
                'entry' => new EntryResource($new_entry),
                ]);
            }
        }
        // if a request is not either a pause,start,resume or end
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
        $entry->delete($entry);
    }

    public function SumationOfDuration()
    {
        $record = user()->records()->find($request->record_id);
        $entry = $record->entries()->orderBy('id','desc')->first();
        $durations = $entry->entry_duration;
        return $durations;
        $sumSeconds = 0;
        foreach($durations as $duration) {
            $explodedTime = explode(':', $duration);
             $seconds = $explodedTime[0]*3600+$explodedTime[1]*60+$explodedTime[2];
             $sumSeconds += $explodedTime;
        }
        $hours = floor($sumSeconds/3600);
        $minutes = floor(($sumSeconds % 3600)/60);
        $seconds = (($sumSeconds%3600)%60);
        $sumTime = $hours.':'.$minutes.':'.$seconds;
        return $sumTime;
    }
}
