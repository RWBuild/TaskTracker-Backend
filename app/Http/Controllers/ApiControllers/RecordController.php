<?php

namespace App\Http\Controllers\ApiControllers;

use App\Record;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\RecordCollection;
use App\Http\Resources\Record as RecordResource;


class RecordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $records = Record::all();
        return new RecordCollection($records);
    }

    public function recordByStatus(Request $request,$recordStatus) 
    {
        $records = [];
        if ($recordStatus=='current') {
            $records = Record::where('is_current',true)->get();
        }

        if ($recordStatus=='opened') {
            $records = Record::where('is_opened',true)->get();
        }

        if ($recordStatus=='finished') {
            $records = Record::where('is_finished',true)->get();
        }

        return new RecordCollection($records);
    }

    public function userRecordByStatus(Request $request,$recordStatus) 
    {
        $records = [];
        $user = user();
        if ($recordStatus=='current') {
            $records = $user->records()->where('is_current',true)->first();
            return new RecordResource($records);
        }

        if ($recordStatus=='opened') {
            $records = $user->records()->where('is_opened',true)->get();
        }

        if ($recordStatus=='finished') {
            $records = $user->records()->where('is_finished',true)->get();
        }

        return new RecordCollection($records);
    }

    public function searchRecord(Request $request)
    {
        $this->validate();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
         'project_id', 
         'user_id', 
         'name', 
         'description', 
         'is_curent', 
         'is_paused', 
         'is_completed'
        ]);
        
        $record = Record::create($request->all());
        return new RecordResource($record);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Record  $record
     * @return \Illuminate\Http\Response
     */
    public function show(Record $record)
    {
        return new RecordResource($record);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Record  $record
     * @return \Illuminate\Http\Response
     */
    public function edit(Record $record)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Record  $record
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Record $record)
    {
        $this->validate($request,[
            'project_id', 
            'user_id', 
            'name', 
            'description', 
            'is_curent', 
            'is_paused', 
            'is_completed'
            ]);
        $record->update($request->all());
        $record = Record::find($request->id);
        
        return response([
                'status' => true,
                'message' => 'record added successfully',
                'record' => new RecordResource($record)
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Record  $record
     * @return \Illuminate\Http\Response
     */
    public function destroy(Record $record)
    {
        $record->delete($record);
    }
}
