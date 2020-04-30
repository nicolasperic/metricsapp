@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ $project->name }}
                        <a href="{{url("users/importUsers/{$project->id}")}}" style="float:right;">Import Users</a>
                        <a href="{{url("sprints/importSprints/{$project->id}")}}" style="float:right;">Import Milestones</a>
                    </div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        Milestones:
                        <ul>
                            @forelse ($project->sprints as $sprint)
                                <li>
                                    <a href="{{url("sprints/{$sprint->id}")}}">{{ $sprint->name}}</a>
                                </li>


                            @empty
                                <p>No sprints assigned to this sprint yet.</p>
                            @endforelse
                        </ul>

                        Users:
                        <ul>
                            @forelse ($project->users as $user)
                                <li>
                                    {{ $user->name }}
                                </li>


                            @empty
                                <p>No users assigned to this sprint yet.</p>
                            @endforelse
                        </ul>


                </div>
            </div>
        </div>
    </div>
@endsection








