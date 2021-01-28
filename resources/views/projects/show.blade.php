@extends('layouts.app')

@section('breadcrumbs',  Breadcrumbs::render('projects.show', $project))

@section('actions')
<a href="{{ url("users/syncUsers/{$project->id}") }}" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> Sync Users</a>
<a href="{{ url("sprints/syncSprints/{$project->id}") }}" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm ml-3"><i class="fas fa-download fa-sm text-white-50"></i> Sync Milestones</a>
@endsection

@section('container-title', $project->name . ' Milestones')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">{{ $project->name }}</h6>
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








