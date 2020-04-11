@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ $sprint->name }}</div>

                    <div class="sprint-stats" style="margin-left: 40px">
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

                        <ul>
                            @forelse ($sprint->tickets as $ticket)
                                <li>
                                    <a href="#">{{ $ticket->name}}</a> Status: {{ $ticket->status}} SP {{ $ticket->story_points }}
                                </li>


                            @empty
                                <p>No tickets assigned to this sprint yet.</p>
                            @endforelse
                        </ul>

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








