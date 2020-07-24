@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">{{ $sprint->name }} <a href="{{url("tickets/importTickets/{$sprint->id}")}}" style="float:right;">Import Tickets</a></div>

                    <div class="sprint-stats" style="margin-left: 40px">
                        - Total Invested Hours: {{ $sprint->getTotalInvestedHours() }} hs<br>
                        - Stories without SP: {{ $sprint->getUserStoriesWithoutStoryPoints() }}<br>
                        - Stories with inconsistent states: {{ $sprint->getUserStoriesWithInconsistentState()  }}<br>
                        - Total Stories: {{ $sprint->getTotalStories() }}<br>
                        - Total tickets: {{ $sprint->getTotalTickets() }}<br>
                        - Completed Story Points: {{ $sprint->getCompletedStoryPoints() }} ({{ $sprint->getPercentCompletedStoryPoints() }}%)<br>
                        - Total story points: {{ $sprint->getTotalStoryPoints() }}<br>

                        - Average Lead Time: {{ $sprint->getAverageLeadTime() }} days <br>
                        - Average Cycle Time: {{ $sprint->getAverageCycleTime() }} days <br>
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








