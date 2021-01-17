@extends('layouts.app')

@section('breadcrumbs',  Breadcrumbs::render('sprints.show',$sprint->projects->first(), $sprint))

@section('container-title', $sprint->getProjectName(). " | $sprint->name")

@section('content')
    <?php
        $percentCompletedStores = $sprint->getPercentCompletedStories();
        $percentCompletedSubtasks = $sprint->getPercentCompletedSubtasks();
    ?>
    <div class="container">

        <div class="row">
            <!-- Total Hours Card -->
            <div class="col-xl-3 col-md-6 mb-4">
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
            <div class="col-xl-3 col-md-6 mb-4">
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
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Stories</div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $sprint->getTotalStories() }}</div>
                                    </div>
                                    <div class="col" data-toggle="tooltip" data-placement="top" title="{{ $sprint->getCompletedStories() }} completed stories {{ $percentCompletedStores }}%">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar {{Helper::getPercentageClass($percentCompletedStores)}}" role="progressbar" style="width: {{ $percentCompletedStores }}%" aria-valuenow="{{ $percentCompletedStores }}" aria-valuemin="0" aria-valuemax="100"></div>
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
            <div class="col-xl-3 col-md-6 mb-4">
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
                                            <div class="progress-bar {{ Helper::getPercentageClass($percentCompletedSubtasks)  }}" role="progressbar" style="width: {{ $percentCompletedSubtasks }}%" aria-valuenow="{{ $percentCompletedSubtasks }}" aria-valuemin="0" aria-valuemax="100"></div>
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
            <!-- Reamaining Estimate (Story Points) Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Remaining Estimate</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sprint->getTotalRemainingEstimate() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estimate Completed (Story Points) Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2" style="border-left-color: #1cc88a !important;">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Completed Estimate</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sprint->getTotalCompletedEstimate() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Estimate (Story Points) Card -->
            <div class="col-xl-3 col-md-6 mb-4">
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
                        <h6 class="m-0 font-weight-bold text-primary">Horas por Mes</h6>
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
                        <h6 class="m-0 font-weight-bold text-primary">Horas por Semana</h6>
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

            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <!-- Card Header - Dropdown -->
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Horas por usuario</h6>
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
                            <canvas id="userHoursChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pie Chart -->
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <!-- Card Header - Dropdown -->
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">US Types</h6>
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
                            <div class="mt-4 small">
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
                <div class="card">
                    <div class="card-header">{{ $sprint->name }} <a href="{{url("tickets/importTickets/{$sprint->id}")}}" style="float:right;">Import Tickets</a></div>

                    <div class="sprint-stats" style="margin-left: 40px">
                        - Total Invested Hours: {{ $sprint->getTotalWorkedHours() }} hs
                        <?php foreach ($sprint->getTimeReport()['monthly_hours'] as $key => $timeReport):?>
                        <pre>    Mes: {{ $key }} ({{ $timeReport['label'] }}) Total: {{$timeReport['hours']}} <?php //echo print_r($timeReport, 1)?>horas <?php echo '('.number_format($timeReport['hours']/$sprint->getTotalWorkedHours()*100, 2).'%)'?></pre>
                    <?php foreach ($timeReport['users'] as $assemblaUserId => $userHours):?>
                                <pre>        {{$userHours['label']}} {{$userHours['hours']}} horas ({{$userHours['tasks']}} tasks)</pre>
                            <?php endforeach;?>
                        <?php endforeach?>

                        <?php
                        if (count($sprint->getTimeReport()['monthly_hours']) ) {
                                //print print_r($sprint->getTimeReport()['monthly_hours'][9]['tickets'],1);
                                //print print_r($sprint->getTimeReport(),1);
                        }


                            //print print_r($sprint->getUserStoriesTypePercentages());
                        ?><br>


                        - Stories without SP: {{ $sprint->getUserStoriesWithoutStoryPoints() }}<br>
                        - Stories with inconsistent states: {{ $sprint->getUserStoriesWithInconsistentState()  }}<br>
                        - Total tickets: {{ $sprint->getTotalTickets() }}<br>
                        - Total Stories: {{ $sprint->getTotalStories() }}<br>
                        - Total Subtasks: {{ $sprint->getTotalSubtasks() }}<br>
                        - Completed Stories: {{$sprint->getCompletedStories()}} {{ $percentCompletedStores  }}%<br>
                        - Completed Subtasks: {{$sprint->getCompletedSubtasks()}} {{ $percentCompletedSubtasks }}%<br>

                        - Completed Tickets: {{ $sprint->getCompletedTickets()->count() }}<br>
                        - Completed Story Points: {{ $sprint->getCompletedStoryPoints() }} ({{ $sprint->getTotalCompletedEstimatePercentage() }}%)<br>
                        - Total story points: {{ $sprint->getTotalStoryPoints() }}<br>

                        - Average Lead Time: {{ $sprint->getAverageLeadTime() }} days <br>
                        - Average Cycle Time: {{ $sprint->getAverageCycleTime() }} days <br>
                    </div>

                    <div class="time-report" style="margin-left: 40px;">


                        <?php
                            //
                            foreach ($sprint->getTimeReport()['weekly_hours'] as $key => $timeReport) {
                            //    print $key .' weekly_hours '.print_r($timeReport,1);
                            }
                        ?>



                    </div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                            ❌: subtasks with invalid status<br/>
                            ⏱: horas trackeadas en la US<br/>
                            🚨: US sin story points estimados
                        <table>
                            <thead>
                                <th>Ticket</th><th>Status</th><th>SP</th><th>Total Hs</th><th>Hs subs</th><th>Hs tracked</th><th># subtasks</th>
                            </thead>
                            @forelse ($sprint->tickets as $ticket)
                                @if($ticket->is_story)

                                    <?php
                                    $status = '';
                                    ?>
                                    @if(count($ticket->getInvalidStatusSubtasks()) > 0)
                                        <?php
                                        $status = '❌';
                                        ?>
                                    @endif

                                    @if($ticket->worked_hours > 0)
                                            <?php
                                            $status .= '⏱';
                                            ?>
                                    @endif

                                        @if($ticket->story_points == 0)
                                            <?php
                                            $status .= '🚨';
                                            ?>
                                        @endif
                                    <tr>
                                        <td><?php echo $status;?>{{ $ticket->number }} {{ Helper::substrIf($ticket->name, 75)}}</td>
                                        <td>{{ $ticket->status }}</td>
                                        <td>{{ $ticket->story_points }}</td>
                                        <td>{{ $ticket->total_invested_hours }}</td>
                                        <td>{{ $ticket->getSubtasksTotalWorkedHours() }}</td>
                                        <td>{{ $ticket->getTotalTrackedTime() }}</td>
                                        <td>{{ $ticket->subtasks()->count() }}</td>
                                    </tr>
                                @endif

                            @empty
                                <p>No tickets assigned to this sprint yet.</p>
                            @endforelse
                        </table>

                        Users:
                            <ul>
                                @forelse ($sprint->users as $user)
                                    <li>
                                        {{ $user->name}}
                                    </li>


                                @empty
                                    <p>No tickets assigned to this sprint yet.</p>
                                @endforelse
                            </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        var percentages = {!! json_encode($sprint->getUserStoriesTypePercentages()) !!};
        var timeReport = {!! json_encode($sprint->getTimeReport()) !!};

        console.log(percentages);
        //console.log(timeReport);
    </script>
@endsection








