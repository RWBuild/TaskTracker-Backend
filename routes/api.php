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
    Route::resource("projects","ApiControllers\ProjectController");

    //Entries
    Route::resource("entries","ApiControllers\EntryController");


});




