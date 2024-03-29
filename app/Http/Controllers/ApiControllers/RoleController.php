<?php

namespace App\Http\Controllers\ApiControllers;

use App\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     //display list of all roles
    public function index()
    {
        //display all roles
        $roles = Role::all();
        return $roles;
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

    // creating a new role
    public function store(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|unique:roles',
            'description'
        ]);

        $role = Role::create([
            'name' => str_replace(" ","",$request->name),
            'description' => $request->description,
            'display_name' => $request->name
        ]);

        return response([
            'success' => true,
            'message' => 'role successfully created',
            'role' => $role
        ],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Role  $role
     * @return \Illuminate\Http\Response
     */

    //updating a role
    public function update(Request $request, Role $role)
    {
        $this->validate($request,[
            'name' => 'required|unique:roles,name,'.$role->id,
            'description'
        ]);

        $role->update([
            'name' => str_replace(" ","",$request->name),
            'description' => $request->description,
            'display_name' => $request->name
        ]);

        return response([
            'success' => true,
            'message' => 'role successfully updated',
            'role' => Role::find($role->id)
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Role  $role
     * @return \Illuminate\Http\Response
     */

    //deleting a role (but you can't delete a role of a super admin) 
    public function destroy(Role $role)
    {
        if ($role->name == 'superadministrator') {
            return response([
                'success' => true,
                'message' => 'You can not delete the super administrator'
            ],400);
        }

        $role->delete();

        return response([
            'success' => true,
            'message' => 'Role successfully deleted'
        ],200);
    }
}
