@extends('layouts.app')

@section('container-title', ' Sprints')

@section('breadcrumbs',  Breadcrumbs::render('sprints'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Your Sprints</div>

                        Open Sprints
                        <ul>
                            @forelse ($openSprints as $sprint)
                                <li>
                                    @foreach ($sprint->projects as $project)
                                        {{$project->name}}
                                    @endforeach
                                    > <a href="{{url("sprints/{$sprint->id}")}}">{{ $sprint->name}}</a>
                                </li>


                            @empty
                                <p>No sprints created yet. Import projects first</p>
                            @endforelse
                        </ul>

                        Closed Sprints
                        <ul>
                            @forelse ($closedSprints as $sprint)
                                <li>
                                    @foreach ($sprint->projects as $project)
                                        {{$project->name}}
                                    @endforeach
                                    > <a href="{{url("sprints/{$sprint->id}")}}">{{ $sprint->name}}</a>
                                </li>


                            @empty
                                <p>No sprints created yet. Import projects first</p>
                            @endforelse
                        </ul>

                </div>
            </div>
        </div>
    </div>
@endsection








