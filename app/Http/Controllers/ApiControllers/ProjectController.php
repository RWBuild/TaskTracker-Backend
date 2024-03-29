<?php

namespace App\Http\Controllers\ApiControllers;

use App\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectCollection;
use App\Http\Resources\Project as ProjectResource;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    //displaying projects which are active only
    public function index()
    {
        $projects = Project::where('active',1)->get();
        return new ProjectCollection($projects);
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

    //creating a new project
    public function store(Request $request)
    {
        $this->validate($request,[
            'name'=>'string|required|unique:projects',
        ]);

        $project = Project::create([
            'name' => $request->name,
            'active' => true
        ]);
        return response ([
            'success' => true,
            'project' => new ProjectResource($project),
            'message' => 'new project created successfully'
        ],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */

    //displaying a single project
    public function show(Project $project)
    {
        return new ProjectResource($project);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */

    //updating a project
    public function update(Request $request, Project $project)
    {  
        $this->validate($request,[
            'name'=>'string|required|unique:projects,name,'.$project->id
        ]);

        $project->update($request->all());
        $project = Project::find($project->id);

        return response ([
            'success' => true,
            'message' => 'Project Updated Successfully',
            'project' => new ProjectResource($project)
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Project $project)
    {
        if($request->active == true){
            $project->update(['active' => false]);
            return response([
                'success' => true,
                'message' => 'project disactivated successfully'
            ], 200);
        }
        $project->update(['active' => true]);
        return response([
            'success' => true,
            'message' => 'project reactivated successfully'
        ], 200);
        
    }
}
