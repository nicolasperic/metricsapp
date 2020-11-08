@extends('layouts.appbis')
@section('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.5/css/bulma.css"/>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Your Tasks for Today</div>
                    <form method="POST" action="/tasks-timer" style="padding: 20px;">
                        @csrf

                        <div class="field">
                            <label class="label" for="title">Project</label>

                            <div class="control">
                                <select name="project">
                                    @foreach($projects as $project)
                                        <option value="{{ $project->project_assembla_id }}" @if($project->project_assembla_id == old('project')) selected @endif>{{ $project->name }}</option>
                                    @endforeach
                                </select>
                                @error('project')
                                <p class="help is-danger">{{ $errors->first('project') }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="field">
                            <label class="label" for="title">Ticket Number</label>

                            <div class="control">
                                <input style="width: 200px;" class="input  @error('ticket_number') is-danger @enderror" type="text" name="ticket_number" id="ticket_number" value="{{ old('ticket_number') }}">

                                <p class="help">Ticket number in which you'll track time (only Subtasks!)</p>
                                @error('ticket_number')
                                <p class="help is-danger">{{ $errors->first('ticket_number') }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button class="button is-link" type="submit">Start Task</button>
                            </div>
                        </div>
                    </form>

                    <ul>
                        @forelse ($tasks as $task)
                            <li>
                                {{ $task->description }}
                            </li>


                        @empty
                            <p>No tasks created for today yet!</p>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection








