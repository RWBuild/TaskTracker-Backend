<?php

namespace App\Http\Controllers\ApiControllers;

use App\Location;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Location as LocationRessource;


class LocationController extends Controller
{
  
    public function index()
    {
        //
    }

    //to see the current location registerer
    public function office_location()
    {
        $location = Location::first();

        if(!$location) {
            return response([
                'success' => false,
                'message' => 'No location has been registered'
            ],404);
        }

        return new LocationRessource($location);
    }

    
    public function create()
    {
        //
    }

    //creating the office location if it does not exists
    public function store(Request $request)
    {
        
        $this->validate($request,[
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'radius' => 'required|numeric'
        ]);
        
        if (Location::all()->count()> 0) {
            return response([
                'success' => false,
                'message' => 'The office must have only one location.delete the current location first'
            ],409);
        }

        $location = Location::create([
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'radius' => $request->radius
        ]);

        return response([
            'success' => true,
            'message' => 'Office location well created.',
            'location' => new LocationRessource($location)
        ],201);
    }

    
    public function show(Location $location)
    {
        
    }

    
    public function edit(Location $location)
    {
        //
    }

    //updating the office location
    public function update(Request $request, $id)
    {
        $this->validate($request,[
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'radius' => 'required|numeric'
        ]);

        $location = Location::find($id);

        if (!$location) {
            return response([
                'success' => false,
                'message' => 'the office location does not exist'
            ],404);
        }

        $location->update([
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'radius' => $request->radius
        ]);

        return response([
            'success' => true,
            'message' => 'Office location well updated.',
            'location' => new LocationRessource(Location::first())
        ],200);
    }

   //delete the office location
    public function destroy($id)
    {
        $location = Location::find($id);

        if (!$location) {
            return response([
                'success' => false,
                'message' => 'the office location does not exist'
            ],404);
        }

        $location->delete();
        return response([
            'success' => true,
            'message' => 'Office location well deleted.',
        ],200);

    }
}
