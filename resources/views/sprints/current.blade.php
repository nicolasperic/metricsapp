@extends('layouts.app')

@section('container-title', ' Sprints')

@section('breadcrumbs',  Breadcrumbs::render('sprints'))

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">Your Current Sprints</div>

                        <ul>
                            <?php $totalEstimate = 0;$totalRemainingEstimate = 0;$totalCompletedEstimate = 0;?>
                            @forelse ($currentSprints as $sprint)
                                <li>
                                    @foreach ($sprint->projects as $project)
                                        <a href="{{url("projects/{$project->id}")}}">{{$project->name}}</a>
                                    @endforeach
                                    > <a href="{{url("sprints/{$sprint->id}")}}">{{ $sprint->name}}</a>  <?= $sprint->getFormattedPlannerType()?>
                                        - Worked Hours {{ $sprint->getTotalWorkedHours() }}
                                        - Remaining Hours {{ $sprint->getTotalWorkingHours() }}
                                        - Remaining Estimate {{ $sprintRemainingEstimate = $sprint->getTotalRemainingEstimate() }}
                                        - Completed Estimate: {{ $sprintCompletedEstimate = $sprint->getTotalCompletedEstimate() }} ({{ $sprint->getTotalCompletedEstimatePercentage() }}%)
                                        - Total Estimate: {{ $sprintTotalEstimate = $sprint->getTotalEstimate() }}
                                        <!-- - Total Stories {{ $sprint->getTotalStories() }}
                                        - Completed Stories {{ $sprint->getCompletedStories() }} -->
                                    <?php
                                        $totalRemainingEstimate += $sprintRemainingEstimate;
                                        $totalCompletedEstimate += $sprintCompletedEstimate;
                                        $totalEstimate += $sprintTotalEstimate;
                                     ?>
                                </li>


                            @empty
                                <p>No currents sprints created yet</p>
                            @endforelse
                        </ul>

                    <!-- Estimate may vary on each space > Story Points or Hours -->
                    <span>Total Remaining Estimate: <?= $totalRemainingEstimate?></span>
                    <span>Total Completed Estimate: <?= $totalCompletedEstimate?></span>
                    <span>Total Estimate: <?= $totalEstimate?></span>
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








