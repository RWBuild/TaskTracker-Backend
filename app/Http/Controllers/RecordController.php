<?php

namespace App\Http\Controllers;

use App\Record;
use Illuminate\Http\Request;
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
        $records = Record::with('user','project')->orderBy('id','DESC')->get();
        return new RecordCollection($records);
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
        return response ([
            'status' => true,
            'record' => $record,
            'message' => 'new record is added successfully'
        ]);
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
        
        return response()->json([
                'status' => true,
                'message' => 'record added successfully',
                'record' => new RecordResource($record)
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Record  $record
     * @return \Illuminate\Http\Response
     */
    public function destroy(Record $record)
    {
        if ($record->delete($record))
            return response()->json([
                'status' => true,
                'message' => 'record deleted successfully',
            ],200);
        
        return response()->json([
            'stauts' => false,
            'message' => 'failed to delete the record',
        ],404);
    }
}
