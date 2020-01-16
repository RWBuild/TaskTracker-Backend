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

    public function update(Request $request, Location $location)
    {
        $this->validate($request,[
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'radius' => 'required|numeric'
        ]);

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

  
    public function destroy(Location $location)
    {
        $location->delete();
        return response([
            'success' => true,
            'message' => 'Office location well deleted.',
        ],200);

    }
}
