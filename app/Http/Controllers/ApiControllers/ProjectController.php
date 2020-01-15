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
    public function store(Request $request)
    {
        $this->validate($request,[
            'name'=>'string|required|unique:projects',
        ]);

        $project = Project::create($request->all());
        return response ([
            'status' => true,
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
    public function update(Request $request, Project $project)
    {  
        $this->validate($request,[
            'name'=>'string|required|unique:projects,name,'.$project->id
        ]);

        $project->update($request->all());
        $project = Project::find($project->id);

        return response ([
            'status' => true,
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
    public function destroy(Project $project)
    {
        $project->active = false;
        $project->save();

        return response([
            'status' => true,
            'message' => 'project successfully deactivated'

        ],204);
    }
}
