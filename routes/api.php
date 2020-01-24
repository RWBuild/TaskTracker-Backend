<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix'=>'auth','middleware' => 'checkin_app_key'], function()
{
   Route::post("/login","ApiControllers\Auth\LoginController@login");
});

Route::group(['middleware' => 'auth:api'], function()
{
    //Users
    Route::get("/user", "ApiControllers\Auth\LoginController@auth_user");
    Route::get("/logout","ApiControllers\Auth\LoginController@logout");
    

    //Location
    Route::get("/get-office-location","ApiControllers\LocationController@office_location");
    

    //user  Checkin and Checkout management
    Route::resource('office-times',"ApiControllers\OfficeTimeController");
    //The last check of the auth user
    Route::get('/last-check','ApiControllers\OfficeTimeController@get_last_check');


    //Records
    Route::resource("records","ApiControllers\RecordController");

    //for authenticated user
    Route::get("/user-record-by-type/{type}","ApiControllers\RecordController@userRecordByType");
    

     //Projects
     Route::resource("projects","ApiControllers\ProjectController")->only(['index']);


    //Entries
    Route::resource("entries","ApiControllers\EntryController");
    


   //Routes for administrators on;y
    Route::group(['prefix'=>'admin','middleware' => ['role:projectmanager|superadministrator']], function()
    {
        //Projects
        Route::resource("projects","ApiControllers\ProjectController");

        //view a specific record
        Route::resource("records","ApiControllers\RecordController")->only(['show']);
        
        //Location
        Route::resource("office-location","ApiControllers\LocationController");

        //Users
        Route::resource('users','ApiControllers\UserController');

        //Get current,open,complete record for a specific user
        Route::get("/specific-user-record/{user_id}/{type}","ApiControllers\RecordController@specificUserRecord");
        // route to get current,open,complete task of all users
        Route::get("/record-by-type/{type}","ApiControllers\RecordController@recordByType");
        //search record filter by name or date
        Route::post('/search-records',"ApiControllers\RecordController@searchRecord");
        
        //Only list of all tash histories and a single task
        Route::resource("task_histories","ApiControllers\TaskHistoryController")->only(['index','show']);

        //To get the list of task histories of a specific task(record)
        Route::get("/record_histories/{record_id}","ApiControllers\TaskHistoryController@record_histories");
        
    });


    Route::group(['prefix'=>'admin','middleware' => ['role:superadministrator']], function()
    {
        //Roles
        Route::resource("roles","ApiControllers\RoleController");

        //a route to assign a role to a user
        Route::post("assign-role","ApiControllers\AdminController@attachRole")
        ->middleware('assign-role-checker');

        //a route to detach a role to a user
        Route::post("unassign-role","ApiControllers\AdminController@detachRole")
        ->middleware('assign-role-checker');

        //Roles
        Route::resource("murugo_users","ApiControllers\MurugoUserController");
    });

});




