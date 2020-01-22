<?php

namespace App\Http\Controllers\ApiControllers;

use App\MurugoUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MurugoUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $murugo_users = MurugoUser::all();

        return response([
            'murugo_users' => $murugo_users
        ]);
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
        $request->validate([
            'murugo_user_id' => 'required|unique:murugo_users'
        ]);

        $murugo_user = MurugoUser::create([
            'murugo_user_id' => $request->murugo_user_id
        ]);

        return response([
            'success' => true,
            'message' => 'murugo user id successfully created',
            'murugo_user' => $murugo_user
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,MurugoUser $murugoUser)
    {
        $request->validate([
            'murugo_user_id' => 'required|unique:murugo_users'
        ]);

        $murugoUser->update([
            'murugo_user_id' => $request->murugo_user_id
        ]);

        return response([
            'success' => true,
            'message' => 'murugo user id successfully updated',
            'murugo_user' => MurugoUser::find($murugoUser->id)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(MurugoUser $murugoUser)
    {
        $murugoUser->delete();

        return response([
            'success' => true,
            'message' => 'murugo user id successfully deleted'
        ]);

    }
}
