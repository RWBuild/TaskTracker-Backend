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


Route::post("/login","ApiControllers\Auth\LoginController@login");

Route::group(['middleware' => ['auth:api']], function()
{
    //Users
    Route::get("/user", "ApiControllers\Auth\LoginController@auth_user");
    Route::get("/logout","ApiControllers\Auth\LoginController@logout");
    Route::resource('users','ApiControllers\UserController');

    //Location
    Route::get("/office-location","ApiControllers\LocationController@office_location");
    

    //user  Checkin and Checkout management
    Route::resource('office-times',"ApiControllers\OfficeTimeController");

    //Records
    Route::resource("records","ApiControllers\RecordController");
    Route::get("/record-by-type/{type}","ApiControllers\RecordController@recordByType");
    Route::get("/user-record-by-type/{type}","ApiControllers\RecordController@userRecordByType");
    Route::post('/search-records',"ApiControllers\RecordController@searchRecord");

     //Projects
     Route::resource("projects","ApiControllers\ProjectController")->only(['index']);


    //Entries
    Route::resource("entries","ApiControllers\EntryController");


   //Routes for administrators on;y
    Route::group(['prefix'=>'admin'], function()
    {
        //Projects
        Route::resource("projects","ApiControllers\ProjectController")
        ->middleware('role:project manager|superadministrator')->except(['index']);
        //Location
        Route::resource("locations","ApiControllers\LocationController")
        ->middleware('role:project manager|superadministrator');
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




