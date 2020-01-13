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
            ]);
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
        
        Location::truncate();//we delete every things from this table

        $location = Location::create([
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'radius' => $request->radius
        ]);

        return response([
            'success' => true,
            'message' => 'Office location well created.',
            'location' => new LocationRessource($location)
        ]);
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
        ]);
    }

  
    public function destroy(Location $location)
    {
        $location->delete();
        return response([
            'success' => true,
            'message' => 'Office location well deleted.',
        ]);

    }
}
