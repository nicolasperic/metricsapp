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
                                    > <a href="{{url("sprints/{$sprint->id}")}}">{{ $sprint->name}}</a>  <?= $sprint->getFormattedPlannerType()?>
                                </li>


                            @empty
                                <p>No sprints created yet.</p>
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
                                <p>No sprints created yet</p>
                            @endforelse
                        </ul>

                </div>
            </div>
        </div>
    </div>
    <style>
        <?php //TODO move styles to CSS/SCSS?>
        .planner-type {
            padding: 3px 5px 3px;
            border-radius: 3px;
            line-height: 17px;
            font-size: 11px;
            cursor: pointer;
            color: white;
            position: relative;
            top: -3px;
        }

        .planner-type.current {
            background-color: rgb(122, 185, 102);
        }

        .planner-type.backlog {
            background-color: rgb(51, 54, 55);
        }
    </style>
@endsection








