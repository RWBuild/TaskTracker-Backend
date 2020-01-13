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
        return new LocationRessource($location);
    }

    
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
        
    }

    
    public function show(Location $location)
    {
        //
    }

    
    public function edit(Location $location)
    {
        //
    }

    public function update(Request $request, Location $location)
    {
        //
    }

  
    public function destroy(Location $location)
    {
        //
    }
}
