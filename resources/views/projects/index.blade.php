@extends('layouts.app')

@section('container-title', 'Projects')

@section('breadcrumbs',  Breadcrumbs::render('projects'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Your Projects (Assembla Spaces)</h6>
                        <a href="{{url("projects/importProjects")}}" style="float: right;">Import Projects</a></div>


                        <ul>
                            @forelse ($projects as $project)
                                <li>
                                    <a href="{{url("projects/{$project->id}")}}">{{ $project->name}}</a>
                                </li>


                            @empty
                                <p>No projects imported yet. What are you waiting for? Import your projects <a href="{{url("projects/importProjects")}}">here!<a/></p>
                            @endforelse
                        </ul>

                </div>
            </div>
        </div>
    </div>
@endsection








