@extends('layouts.app')

@section('breadcrumbs',  Breadcrumbs::render('projects.show', $project))

@section('container-title', $project->name . ' Milestones')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">{{ $project->name }}</h6>
                        <a href="{{url("users/importUsers/{$project->id}")}}" style="float:right;">Import Users</a>
                        <a href="{{url("sprints/importSprints/{$project->id}")}}" style="float:right;margin-right: 5px;">Import Milestones</a>
                    </div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        Open Milestones:
                        <ul>
                            @forelse ($project->getOpenSprints as $sprint)
                                <li>
                                    <a href="{{url("sprints/{$sprint->id}")}}">{{ $sprint->name}}</a> <?= $sprint->getFormattedPlannerType()?>
                                </li>


                            @empty
                                <p>No sprints assigned to this project yet.</p>
                            @endforelse
                        </ul>

                        Closed Milestones:
                        <ul>
                            @forelse ($project->getClosedSprints as $sprint)
                                <li>
                                    <a href="{{url("sprints/{$sprint->id}")}}">{{ $sprint->name}}</a>
                                </li>


                            @empty
                                <p>No sprints assigned to this project yet.</p>
                            @endforelse
                        </ul>


                        Assembla Team Members:
                        <ul>
                            @forelse ($project->assemblaUsers as $user)
                                <li>
                                    {{ $user->name }}
                                </li>


                            @empty
                                <p>No assembla users imported yet.</p>
                            @endforelse
                        </ul>


                        App Users:
                        <ul>
                            @forelse ($project->users as $user)
                                <li>
                                    {{ $user->name }}
                                </li>


                            @empty
                                <p>No users assigned to this project yet.</p>
                            @endforelse
                        </ul>
                    </div>
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








