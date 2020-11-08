@extends('layouts.app')

@section('container-title', 'Projects')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Your Projects <a href="{{url("projects/importProjects")}}" style="float:right;">Import Projects</a></div>


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








