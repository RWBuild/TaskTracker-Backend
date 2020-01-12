<?php

namespace App\Http\Controllers\ApiControllers;

use App\Record;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
        $user = Auth::user()->id;
        $is_checked = Auth::user()->has_checked;
        $this->validate($request,[
            'project_id'=>'integer|required',
            'name'=>'string|required'
        ]);
        if($is_checked == 0)
        {
            return response()->json([
                'success' => false,
                'message' => 'checkin first',
            ]);
        }
        $record = Record::create([
            'name' => $request->name,
            'project_id' => $request->project_id,
            'user_id' => $user,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'record created',
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
        return new RecordResource($resource);
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
        $this->validate([
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

    public function record_by_type($type)
    {
        
    }
}
