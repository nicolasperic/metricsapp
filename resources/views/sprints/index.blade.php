@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Your Sprints</div>


                        <ul>
                            @forelse ($sprints as $sprint)
                                <li>
                                    <a href="{{url("sprints/{$sprint->id}")}}">{{ $sprint->name}}</a>
                                </li>


                            @empty
                                <p>No sprints created yet. What are you waiting for? Create your new Sprint here!</p>
                            @endforelse
                        </ul>

                </div>
            </div>
        </div>
    </div>
@endsection








