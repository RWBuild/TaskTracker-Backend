<?php

namespace App\Http\Controllers\ApiControllers;

use DateTime;
use App\Entry;
use App\Record;
use Carbon\Carbon;
use App\Classes\CreateEntryHelper;
use App\Classes\UpdateEntryHelper;
use App\Classes\DeleteEntryHelper;
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
            'record_id'=>'required|integer',
            'entry_type'=>'required|string',
            'entry_time'=>'required|date',
        ]);

        $record = user()->records()
                       ->find($request->record_id);

        $entry_helper = new CreateEntryHelper($record);
        
        return $entry_helper->response();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Entry  $entry
     * @return \Illuminate\Http\Response
     */
    public function show(Entry $entry)
    {
        $entry = Entry::find($entry);
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
            'entry_time'=>'required|date',
        ]);
        
        $update_entry_helper = new UpdateEntryHelper($entry);

        return $update_entry_helper->response();
        
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
        $delete_entry_helper = new DeleteEntryHelper($entry);
        
        return $delete_entry_helper->response();
    }
}
