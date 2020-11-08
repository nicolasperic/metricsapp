@extends('layouts.app')
<?php
$users = [
        'c5sp9uUXyr6Ok5cK-zJOy8' => 'Julieta Pisani',
        'd8r95QiVer6zj-aH8tHBnc' => 'Franco Aller',
        'cvixt811Gr4PBcacwqjQYw' => 'Nicolás Peric',
        'aAbtrS7fKr6y_dcP_HzTya' => 'Barbara Irizaga',
        'dNWJBO9war45rbacwqjQXA' => 'Elina Perez',
        'cc2NS0ZTSr4RS_acwqjQYw' => 'Jonatan Mayorano',
        'dBYqHcg2Cr5PRcdmr6CpXy' => 'Santiago Tolosa',
        'brVttgsFOr543cdmr6QqzO' => 'Emanuel Arcos',
        'buOwlo1uer45NdacwqjQWU' => 'Martín Granate',
        'ajLyFEiVir6A3ccK-zJOy8' => 'Federico Ackerley',
        'athUCe0pCr5OFcacwqEsg8' => 'Mariano Zunini',
        'c6u2Cuuu4r6AFdbK8JiBFu' => 'Martin Perrotta',
        'aW_vfY1FGr6ioeaH8tHBnc' => 'Brenda Herrada',
        'aVzzeMlw0r6RhdaIC_Qgzw' => 'Nicolas Lavaggi',
        'aSD9Sgwzqr6OoBaH8tHBnc' => 'Ezequiel Alvian',
        'aDiA_Cb2Wr6iNcacwqjQYw' => 'Matias Rodriguez',
        'a5Uwc0GEyr45yTacwqEsg8' => 'Alejandro Borria',
        'bYoBk2IxKr5PNcdmr6QqzO' => 'Diego Piu',
        'b_V2Si_JCr6lldaH8tHBnc' => 'Matias Wagner',
        'dUHuyGkPGr44k-acwqEsg8' => 'Pedro Rigoli',
        'ddsWca79Wr44oYacwqjQXA' => 'Nicolas Alejandro Gandara',
        'dzBlqaLhKr5O16acwqEsg8' => 'Esteban Campos',
        'arrHT2RRer54rQdmr6QqzO' => 'Mariana Rodriguez'
];
?>

@section('container-title', $sprint->getProjectName(). " | $sprint->name")


@section('content')
    <div class="container">

        <div class="row">
            <!-- Total Hours Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Horas Totales</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $sprint->getTotalWorkedHours() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                                    <div class="col" data-toggle="tooltip" data-placement="top" title="{{ $sprint->getCompletedStories() }} completed stories {{ $sprint->getPercentCompletedStories() }}%">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $sprint->getPercentCompletedStories() }}%" aria-valuenow="{{ $sprint->getPercentCompletedStories() }}" aria-valuemin="0" aria-valuemax="100"></div>
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
                                    <div class="col" data-toggle="tooltip" data-placement="top" title="{{ $sprint->getCompletedSubtasks() }} completed subtasks {{ $sprint->getPercentCompletedSubtasks() }}%">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $sprint->getPercentCompletedSubtasks() }}%" aria-valuenow="{{ $sprint->getPercentCompletedSubtasks() }}" aria-valuemin="0" aria-valuemax="100"></div>
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
            <div class="col-xl-4 col-lg-5">
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
                        <div class="chart-pie pt-4 pb-2">
                            <canvas id="usTypeCount"></canvas>
                        </div>
                        <div class="mt-4 text-center small">
                            @foreach($sprint->getUserStoriesTypePercentages() as $i => $usType)
                                <span class="mr-2">
                                    <i class="fas fa-circle" style="color: {{$sprint->getTypeColor($usType['label'])}}"></i> {{$usType['label']}}
                                </span>
                            @endforeach
                            <br/><span>Externo: cantidad de us de este tipo</span><br>
                            <span>Iterno: cantidad de horas de este tipo</span>
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
                            <pre>        {{$users[$assemblaUserId]}} {{$userHours['hours']}} horas ({{$userHours['tasks']}} tasks)</pre>
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
                        - Completed Stories: {{$sprint->getCompletedStories()}} {{ $sprint->getPercentCompletedStories()  }}%<br>
                        - Completed Subtasks: {{$sprint->getCompletedSubtasks()}} {{ $sprint->getPercentCompletedSubtasks() }}%<br>

                        - Completed Tickets: {{ $sprint->getCompletedTickets()->count() }}<br>
                        - Completed Story Points: {{ $sprint->getCompletedStoryPoints() }} ({{ $sprint->getPercentCompletedStoryPoints() }}%)<br>
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
                                        <td><?php echo $status;?>{{ $ticket->number }} {{ $ticket->getFormattedName() }}</td>
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








