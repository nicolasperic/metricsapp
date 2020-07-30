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


@section('content')
    <div class="container">
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

                        <?php print print_r($sprint->getTimeReport()['monthly_hours'][6]['tickets'],1);?><br>


                        - Stories without SP: {{ $sprint->getUserStoriesWithoutStoryPoints() }}<br>
                        - Stories with inconsistent states: {{ $sprint->getUserStoriesWithInconsistentState()  }}<br>
                        - Total Stories: {{ $sprint->getTotalStories() }}<br>
                        - Total tickets: {{ $sprint->getTotalTickets() }}<br>
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
@endsection








