@extends('layouts.app')
<?php $project = $sprint->projects->first();?>
@section('breadcrumbs',  Breadcrumbs::render('sprints.show', $project, $sprint))

@section('container-title', $project->name. " | $sprint->name")
@section('container-class','sprint-container')
@section('content')
    <?php
        $timeReport = $sprint->getTimeReport();
        $percentCompletedStories = $sprint->getPercentCompletedStories();
        $percentCompletedSubtasks = $sprint->getPercentCompletedSubtasks();
        $totalCompletedTickets = $sprint->getTotalCompletedTickets();
        $totalTickets = $sprint->getTotalTickets();
        $percentCompletedTickets = Helper::getPercentageValue($totalCompletedTickets, $totalTickets);
    ?>

    <div class="actions" style="position: relative; top: -55px; width: 80%;">
        <a href="{{ url("tickets/syncTickets/{$sprint->id}") }}" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> Sync Tickets</a>
        <small><i>Last synced on {{$sprint->updated_at }} ({{Helper::getTimeDiff($sprint->updated_at)}})</i></small>
    </div>
    <div class="row">
        <!-- Total Hours Card -->
        <div class="col-xl-3 col-md-6 mb-4 stats-card">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Worked Hours</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sprint->getTotalWorkedHours() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-end fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Remaining Hours Card -->
        <div class="col-xl-3 col-md-6 mb-4 stats-card">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Remaining Hours</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sprint->getTotalWorkingHours() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-start fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- US Card -->
        <div class="col-xl-3 col-md-6 mb-4 stats-card">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Stories</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $sprint->getTotalStories() }}</div>
                                </div>
                                <div class="col" data-toggle="tooltip" data-placement="top" title="{{ $sprint->getCompletedStories() }} completed stories {{ $percentCompletedStories }}%">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar {{Helper::getPercentageClass($percentCompletedStories)}}" role="progressbar" style="width: {{ $percentCompletedStories }}%" aria-valuenow="{{ $percentCompletedStories }}" aria-valuemin="0" aria-valuemax="100">{{$percentCompletedStories}}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subtasks Card -->
        <div class="col-xl-3 col-md-6 mb-4 stats-card">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Subtasks</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $sprint->getTotalSubtasks() }}</div>
                                </div>
                                <div class="col" data-toggle="tooltip" data-placement="top" title="{{ $sprint->getCompletedSubtasks() }} completed subtasks {{ $percentCompletedSubtasks }}%">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar {{ Helper::getPercentageClass($percentCompletedSubtasks)  }}" role="progressbar" style="width: {{ $percentCompletedSubtasks }}%" aria-valuenow="{{ $percentCompletedSubtasks }}" aria-valuemin="0" aria-valuemax="100">{{$percentCompletedSubtasks}}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Total Tickets Card -->
        <div class="col-xl-3 col-md-6 mb-4 stats-card">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Tickets</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $totalTickets }}</div>
                                </div>
                                <div class="col" data-toggle="tooltip" data-placement="top" title="{{ $totalCompletedTickets }} completed tickets {{ $percentCompletedTickets }}%">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar {{Helper::getPercentageClass($percentCompletedTickets)}}" role="progressbar" style="width: {{ $percentCompletedTickets }}%" aria-valuenow="{{ $percentCompletedTickets }}" aria-valuemin="0" aria-valuemax="100">{{$percentCompletedTickets}}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Remaining Estimate (Story Points) Card -->
        <div class="col-xl-3 col-md-6 mb-4 stats-card">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Remaining Estimate</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $totalRemainingEstimate = $sprint->getTotalRemainingEstimate() }}</div>
                                </div>
                                <?php $totalCompletedEstimatePercentage = $sprint->getTotalCompletedEstimatePercentage()?>
                                <?php $remainingEstimatePercentage = ($totalCompletedEstimatePercentage != 0)?100 - $totalCompletedEstimatePercentage:0?>
                                <div class="col" data-toggle="tooltip" data-placement="top" title="{{ $totalRemainingEstimate }} remaining estimate {{ $remainingEstimatePercentage }}%">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar {{Helper::getPercentageClass($remainingEstimatePercentage, true)}}" role="progressbar" style="width: {{ $remainingEstimatePercentage }}%" aria-valuenow="{{ $remainingEstimatePercentage }}" aria-valuemin="0" aria-valuemax="100">{{$remainingEstimatePercentage}}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Estimate (Story Points) Card -->
        <div class="col-xl-3 col-md-6 mb-4 stats-card">
            <div class="card border-left-info shadow h-100 py-2" style="border-left-color: #1cc88a !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Completed Estimate</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $totalCompletedEstimate = $sprint->getTotalCompletedEstimate() }}</div>
                                </div>
                                <div class="col" data-toggle="tooltip" data-placement="top" title="{{ $totalCompletedEstimate }} completed estimate {{ $totalCompletedEstimatePercentage }}%">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar {{Helper::getPercentageClass($totalCompletedEstimatePercentage)}}" role="progressbar" style="width: {{ $totalCompletedEstimatePercentage }}%" aria-valuenow="{{ $totalCompletedEstimatePercentage }}" aria-valuemin="0" aria-valuemax="100">{{$totalCompletedEstimatePercentage}}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Estimate (Story Points) Card -->
        <div class="col-xl-3 col-md-6 mb-4 stats-card">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Estimate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sprint->getTotalEstimate() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Hours per Month</h6>
                    <!--div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Dropdown Header:</div>
                            <a class="dropdown-item" href="#">Action</a>
                            <a class="dropdown-item" href="#">Another action</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#">Something else here</a>
                        </div>
                    </div-->
                </div>
                <!-- Card Body -->
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="sprintMonthlyHoursChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Hours per Week</h6>
                    <!--div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Dropdown Header:</div>
                            <a class="dropdown-item" href="#">Action</a>
                            <a class="dropdown-item" href="#">Another action</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#">Something else here</a>
                        </div>
                    </div-->
                </div>
                <!-- Card Body -->
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="sprintWeeklyHoursChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6 mb-4">
            <div id="flip-card">
                <div class="card shadow mb-4 flip-card-front">
                    <!-- Card Header - Dropdown -->
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Hours per user</h6>

                        <div class="dropdown no-arrow">
                            <a class="flip-card" href="javascript:;" id="flip-card-btn-turn-to-back" data-toggle="tooltip" data-placement="top" title="Flip card for raw data">
                                <i class="fas fa-sync fa-sm fa-fw text-gray-400"></i>
                            </a>
                        </div>
                    </div>
                    <!-- Card Body -->
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="userHoursBarChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4 flip-card-back">
                    <!-- Card Header - Dropdown -->
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Hours per user Raw data</h6>
                        <div class="dropdown no-arrow">
                            <a class="flip-card" href="javascript:;" id="flip-card-btn-turn-to-front">
                                <i class="fas fa-sync fa-sm fa-fw text-gray-400"></i>
                            </a>
                        </div>
                    </div>
                    <!-- Card Body -->
                    <div class="card-body hours-per-user-table-container">
                        <table class="table table-striped hours-per-user-table">
                            <thead>
                            <tr>
                                <th scope="col"></th>
                                <th scope="col">User</th>
                                <th scope="col">Hours</th>
                                <th scope="col">Tasks</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $totalWorkedHours = $sprint->getTotalWorkedHours()?>
                            @foreach($timeReport['user_hours'] as $user)
                                <tr>
                                    <td style="width: 32px; padding: 8px 0 0 5px;">
                                        <img class="rounded-circle" style="width: 32px; height: 32px;" src="{{$user['picture']}}"/>
                                    </td>
                                    <td>{{$user['label']}}</td>
                                    <td>{{$user['total_hours']}} hours ({{ Helper::getPercentageValue($user['total_hours'], $totalWorkedHours, $decimals = 2) }}%)</td>
                                    <td>{{$user['total_tasks']}} tasks</td>
                                </tr>
                            @endforeach
                            <tbody>
                        </table>
                    </div>
                </div>
            </div>



        </div>

        <style>

            .dropdown.no-arrow .flip-card i.fas.fa-sync.fa-sm.fa-fw.text-gray-400:hover {
                color: #a8aab7 !important;
            }

           /* flip-card > [flip-card-front] y [flip-card-back]*/
            #flip-card {
                width: 100%;
                height: 100%;
                /*min-height: 410px;/*workaround*/

                -o-transition: all 1s ease-in-out;
                -webkit-transition: all 1s ease-in-out;
                -ms-transition: all 1s ease-in-out;
                transition: all 1s ease-in-out;
                -o-transform-style: preserve-3d;
                -webkit-transform-style: preserve-3d;
                -ms-transform-style: preserve-3d;
                transform-style: preserve-3d;
            }
            .do-flip {
                -o-transform: rotateY(-180deg);
                -webkit-transform: rotateY(-180deg);
                -ms-transform: rotateY(-180deg);
                transform: rotateY(-180deg);
            }
            #flip-card-btn-turn-to-back, #flip-card-btn-turn-to-front {



            }
            #flip-card .flip-card-front, #flip-card .flip-card-back{
                width: 100%;
                height: 100%;
                position: absolute;
                -o-backface-visibility: hidden;
                -webkit-backface-visibility: hidden;
                -ms-backface-visibility: hidden;
                backface-visibility: hidden;
                z-index: 2;
            }

            #flip-card .flip-card-back {
                -o-transform: rotateY(180deg);
                -webkit-transform: rotateY(180deg);
                -ms-transform: rotateY(180deg);
                transform: rotateY(180deg);
            }
        </style>
        <script type="text/javascript">



            if ($('.chart-area').height() <= 320) {
                $('#flip-card').height($('.chart-area').height() + 100);
            }
            $(window).on("resize", function () {
                if ($('.chart-area').height() <= 320) {
                    $('#flip-card').height($('.chart-area').height() + 100);
                }
            });


            document.addEventListener('DOMContentLoaded', function(event) {

                document.getElementById('flip-card-btn-turn-to-back').style.visibility = 'visible';
                document.getElementById('flip-card-btn-turn-to-front').style.visibility = 'visible';

                document.getElementById('flip-card-btn-turn-to-back').onclick = function() {
                    document.getElementById('flip-card').classList.toggle('do-flip');
                };

                document.getElementById('flip-card-btn-turn-to-front').onclick = function() {
                    document.getElementById('flip-card').classList.toggle('do-flip');
                };

            });
        </script>

        <!-- Pie Chart -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">User Stories Types</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Dropdown Header:</div>
                            <a class="dropdown-item" href="#">Action</a>
                            <a class="dropdown-item" href="#">Another action</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#">Something else here</a>
                        </div>
                    </div>
                </div>
                <!-- US Type Body -->
                <div class="card-body">
                    <div class="col-xl-6 col-lg-6" style="float: left;">
                        <div class="chart-pie pt-4 pb-2">
                            <canvas id="usTypeCount"></canvas>
                        </div>
                        <div class="text-center small">
                            <span>External: amount of US for each type</span><br>
                            <span>Internal: amount of hours for each type</span>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6" style="float: right; padding-right: 0px;">
                        <div class="user-stories-types mt-2 small">
                            @foreach($sprint->getUserStoriesTypePercentages() as $i => $usType)
                                <div class="user-story-type">
                                    <i class="fas fa-circle" style="color: {{$usType['color']['main']}}"></i>
                                    <span class="type-label">{{$usType['label']}}</span>
                                    <div class="user-story-type-stats">
                                        <span class="count">{{($usType['total']>1)? $usType['total'].' user stories': $usType['total'].' user story'}}  ({{$usType['count_percentage']}}%)</span>
                                        <span class="hours">{{$usType['total_invested_hours']}} hours ({{$usType['hours_percentage']}}%)</span>
                                    </div>

                                </div>


                            <style>
                                .user-story-type {

                                }
                                .type-label {
                                    text-transform: uppercase;
                                    font-weight: bold;
                                    font-size: 1.2em;
                                }
                                .count, .hours {
                                    display: block;
                                }
                            </style>




                                <!-- span class="mr-2">
                                <i class="fas fa-circle" style="color: {{$usType['color']['main']}}"></i> {{$usType['label']}}
                            </span -->
                            @endforeach
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>


    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Tickets in {{ $sprint->name }}</h6>

                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="small">
                    <?php //TODO generate the getUserStoriesWithInconsistentState working with eager data ?>
                        <!--span class="validator">❌  subtasks with invalid status <strong>({{ 1/*$sprint->getUserStoriesWithInconsistentState()*/}})</strong></span-->
                        <span class="validator">⏱   tracked time directly on User Story</span>
                        <span class="validator">🚨  User Story without estimate <strong>({{ $sprint->getUserStoriesWithoutStoryPoints() }})</strong></span>
                    </div-->


                        <div>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th scope="col"></th>
                                <th scope="col">Ticket</th>
                                <th scope="col">Status</th>
                                <th scope="col">Estimate</th>
                                <th scope="col">Hours</th>
                            </tr>
                            </thead>
                            <tbody>

                        @forelse ($sprint->tickets as $ticket)
                            @if($ticket->is_story)
                                <?php
                                $status = '';
                                ?>
                                <!-- if(count($ticket->getInvalidStatusSubtasks()) > 0)
                                    <?php //$status = '❌'; ?>
                                endif -->

                                @if($ticket->worked_hours > 0)
                                    <?php $status .= '⏱'; ?>
                                @endif

                                @if($ticket->estimate == 0)
                                    <?php $status .= '🚨'; ?>
                                @endif
                                <tr>
                                    <th style="padding: 0.75rem 0px 0px 0px; letter-spacing: 2px; text-align: center;"> <span class="small">{{$status}}</span></th>
                                    <th scope="row">
                                        <a href="https://app.assembla.com/spaces/{{$project->wikiname}}/tickets/{{$ticket->number}}" target="_blank">{{ $ticket->number }} {{ Helper::substrIf($ticket->name, 75)}}</a>
                                    </th>
                                    <td>{{ $ticket->status }}</td>
                                    <td>{{ $ticket->estimate }}</td>
                                    <td>{{ $ticket->total_invested_hours }}</td>
                                </tr>
                            @endif

                        @empty
                            <p>No tickets assigned to this sprint yet.</p>
                        @endforelse
                            </tbody>
                        </table>
                        </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var sprintName = "{!! $sprint->name !!}";
        var percentages = {!! json_encode($sprint->getUserStoriesTypePercentages()) !!};
        var timeReport = {!! json_encode($timeReport) !!};
        console.log(timeReport);
    </script>
@endsection








