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

    //Records
    Route::resource("records","ApiControllers\RecordController")->except(['show']);
    

    //for authenticated user
    Route::get("/user-record-by-type/{type}","ApiControllers\RecordController@userRecordByType");
    

     //Projects
     Route::resource("projects","ApiControllers\ProjectController")->only(['index']);


    //Entries
    Route::resource("entries","ApiControllers\EntryController");
    Route::get("summation/{records}","ApiControllers\EntryController@SumationOfDuration");


   //Routes for administrators on;y
    Route::group(['prefix'=>'admin','middleware' => ['role:projectmanager|superadministrator']], function()
    {
        //Projects
        Route::resource("projects","ApiControllers\ProjectController");
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
    });

});




