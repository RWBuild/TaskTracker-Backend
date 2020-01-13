<?php

namespace App\Http\Controllers\ApiControllers;

use App\Record;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\RecordCollection;
use App\Http\Resources\Record as RecordResource;
use Illuminate\Support\Facades\Validator;

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

    // display the current,opened and finished records of all users
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

    // public function userRecordByStatus(Request $request,$recordStatus) 
    // {
    //     $records = [];
    //     $user = user();
    //     if ($recordStatus=='current') {
    //         $records = $user->records()->where('is_current',true)->first();
    //         return new RecordResource($records);
    //     }

    //     if ($recordStatus=='opened') {
    //         $records = $user->records()->where('is_opened',true)->get();
    //     }

    //     if ($recordStatus=='finished') {
    //         $records = $user->records()->where('is_finished',true)->get();
    //     }

    //     return new RecordCollection($records);
    // }

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
        $user = Auth::user()->id;
        $is_checked = Auth::user()->has_checked;
        $this->validate($request,[
            'project_id'=>'integer|required',
            'name'=>'string|required',
            'start_date' => 'required',
            'start_time' => 'required',
        ]);
        if($is_checked == 0)
        {
            return response()->json([
                'success' => false,
                'message' => 'checkin first',
            ]);
        }
        // return $request->all();
        $record = Record::create([
            'name' => $request->name,
            'project_id' => $request->project_id,
            'user_id' => $user,
            'start_date' =>$request->start_date,
            'start_time' =>$request->start_time,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'record created',
            'record' => new RecordResource($record),
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
       
        $this->validate($request, array(
            'project_id'=>'integer|required',
            'name'=>'string|required',
            'start_date' => 'required',
            'start_time' => 'required',
        ));
        // return $request->all();
        $record->update([
            'project_id' => $request->project_id,
            'name' => $request->name,
            'start_date' =>$request->start_date,
            'start_time' =>$request->start_time,
        ]);
        return response([
                'status' => true,
                'message' => 'record updated successfully',
                'record' => new RecordResource($record),
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
        return response([
            'status' => true,
            'message' => 'record deleted successfully',
        ]);
    }

    public function record_by_type($type)
    {

        if($type != 'open' && $type != 'current' && $type != 'completed')
        {
            return response()->json([
                'success' => false,
                'message' => 'invalid request',
            ]);
        }
        
        else if($type == 'current')
        {
            $record = Record::where('is_current',1)->first();
            return new RecordResource($record);
        }
       
        else if($type == 'open')
        {
            $record = Record::where('is_opened',1)->get();
            return new RecordCollection($record);
        }

        else if($type == 'completed')
        {
            $record = Record::where('is_finished',1)->get();
            return new RecordCollection($record);
        }
    
    }
}
