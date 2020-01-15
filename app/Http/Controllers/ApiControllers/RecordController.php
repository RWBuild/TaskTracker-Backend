<?php

namespace App\Http\Controllers\ApiControllers;

use App\User;
use App\Record;
use App\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\RecordCollection;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Record as RecordResource;

class RecordController extends Controller
 {
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    protected $knownTypes = ['current', 'open', 'complete'];

    //returning a list of all records

    public function index()
 {
        $records = Record::all();
        return new RecordCollection( $records );
    }

    // display the current, opened and finished records of all users

    public function recordByType( $recordType ) 
 {
        $records = [];

        if ( in_array( $recordType, $this->knownTypes ) == false ) {
            return response( [
                'success' => false,
                'message' => 'the record type must be : current,open or complete '
            ] );
        }

        if ( $recordType == 'current' ) {
            $records = Record::where( 'is_current', true )->get();
        }

        if ( $recordType == 'open' ) {
            $records = Record::where( 'is_opened', true )->get();
        }

        if ( $recordType == 'complete' ) {
            $records = Record::where( 'is_finished', true )->get();
        }

        return response( [
            'success' => true,
            'records' => new RecordCollection( $records )
        ] );
    }

    //a function to provide : current , open and complete task of a specific user
    public function specificUserRecord($user_id,$recordType) 
    {
        $records = [];
        $user = User::findOrFail($user_id);
       
        if (in_array($recordType, $this->knownTypes)==false) {
           return response([
               'success' => false,
               'message' => 'the record type must be : current,open or complete '
           ]);
        }

        if ($recordType=='current') {
            $record = $user->records()->where('is_current',true)->first();

            if (!$record) {
                return response([
                    'success' => false,
                    'message' => "User doesn't have any current task he is working on"
                ],404);
            }

            return response([
                'success' => true,
                'record' => new RecordResource($record),
                'user_names' =>  $user->names
            ]);
        }

        if ($recordType=='open') {
            $records = $user->records()->where('is_opened',true)->get();
        }

        if ($recordType=='complete') {
            $records = $user->records()->where('is_finished',true)->get();
        }



        return response([
            'success' => true,
            'records' => new RecordCollection($records)
        ]);
    }


    //search a record by name or date | or search record which belongs to a project
    public function searchRecord(Request $request)
    {
        $this->validate($request, [
            'record_value' => "required"
        ]);

        //Find the id of the project that may be looking for
        $project = Project::where("name",'like','%'.$request->record_value.'%')->first();
        $records = [];

        if ($project) {// once find project, search where record belongs to the project

            $records = Record::where('name','like','%'.$request->record_value.'%')
            ->orWhere('start_date',$request->record_value)
            ->orWhere('project_id',$project->id)
            ->get();
        }
        else{// 
            $records = Record::where('name','like','%'.$request->record_value.'%')
            ->orWhere('start_date',$request->record_value)
            ->get();
        }

        return response( [
            'success' => true,
            'records' => new RecordCollection( $records )
        ] );
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

    //creating a new record

    public function store( Request $request )
 {
        $user = user();
        $is_checked = $user->has_checked;
        $this->validate( $request, [
            'project_id'=>'integer|required',
            'name'=>'string|required',
            'start_date' => 'required|date',
            'start_time' => 'required',
        ] );
        if ( $is_checked == 0 )
        {
            return response()->json( [
                'success' => false,
                'message' => 'checkin first',
            ] );
        }
        $record = new Record( [
            'name' => $request->name,
            'project_id' => $request->project_id,
            'user_id' => $user->id,
            'is_current' => 1,
            'is_opened' => 1,
            'is_finished' => 0,
            'start_date' =>$request->start_date,
            'start_time' =>$request->start_time,
        ] );
        $record->save();

        return response()->json( [
            'success' => true,
            'message' => 'record created',
            'record' => new RecordResource( $record ),
        ] );
    }
    /**
    * Display the specified resource.
    *
    * @param  \App\Record  $record
    * @return \Illuminate\Http\Response
    */

    public function show( Record $record )
 {
        return new RecordResource( $record );
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  \App\Record  $record
    * @return \Illuminate\Http\Response
    */

    public function edit( Record $record )
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

    //updating a record

    public function update( Request $request, Record $record )
    {

        $this->validate( $request, array(
            'project_id'=>'integer|required',
            'name'=>'string|required',
            'start_date' => 'required',
            'start_time' => 'required',
        ) );

        $record->update( [
            'project_id' => $request->project_id,
            'name' => $request->name,
            'start_date' =>$request->start_date,
            'start_time' =>$request->start_time,
        ] );
        return response( [
            'status' => true,
            'message' => 'record updated successfully',
            'record' => new RecordResource( $record ),
        ] );
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  \App\Record  $record
    * @return \Illuminate\Http\Response
    */

    //deleting a record

    public function destroy( Record $record )
    {
        $record->delete( $record );
        return response( [
            'status' => true,
            'message' => 'record deleted successfully',
        ] );
    }

    //view opened, current and completed records of an authenticated user

    public function userRecordByType( $recordType )
 {

        if ( in_array( $recordType, $this->knownTypes ) == false ) {
            return response( [
                'success' => false,
                'message' => 'the record type must be : current,open or complete '
            ] );
        }
        $user = user();

        if ( $recordType == 'current' )
 {
            $record = $user->records()->where( 'is_current', 1 )->first();
            return new RecordResource( $record );
        }

        $records = [];

        if ( $recordType == 'open' )
 {
            $records = $user->records()->where( 'is_opened', 1 )->get();
        }

        if ( $recordType == 'complete' )
 {
            $records = $user->records()->where( 'is_finished', 1 )->get();
        }

        return new RecordCollection( $records );

    }
}
