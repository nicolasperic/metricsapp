@extends('layouts.app')

@section('container-title', ' Sprints')

@section('breadcrumbs',  Breadcrumbs::render('sprints'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Your Milestones</h6>
                    </div>

                        Open Milestones
                        <ul>
                            @forelse ($openSprints as $sprint)
                                <li>
                                    <?php $project = $sprint->getProject()?>
                                    {{$project->name}}

                                    > <a href="{{ route('sprints.show', [$project->wikiname, $sprint->sprint_assembla_id]) }}">{{ $sprint->name}}</a>  <?= $sprint->getFormattedPlannerType()?>
                                </li>


                            @empty
                                <p>No sprints created yet.</p>
                            @endforelse
                        </ul>

                        Closed Milestones
                        <ul>
                            @forelse ($closedSprints as $sprint)
                                <li>
                                    <?php $project = $sprint->getProject()?>
                                    {{$project->name}}

                                    > <a href="{{ route('sprints.show', [$project->wikiname, $sprint->sprint_assembla_id]) }}">{{ $sprint->name}}</a>
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








