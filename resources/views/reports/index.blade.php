@extends('layouts.appbis')
@section('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.5/css/bulma.css"/>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Hours by User Story Report</div>
                    <form method="POST" action="/reports" style="padding: 20px;">
                        @csrf

                        <div class="field">
                            <label class="label" for="title">Project</label>


                            <div class="control">
                                @if (count($projects))
                                    <select name="project">
                                        @foreach($projects as $project)
                                            <option value="{{ $project->project_assembla_id }}" @if($project->project_assembla_id == old('project')) selected @endif>{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('project')
                                    <p class="help is-danger">{{ $errors->first('project') }}</p>
                                    @enderror
                                @else
                                    No projects yet, <a href="{{url("projects/importProjects")}}">Import Projects</a>
                                @endif
                            </div>
                        </div>

                        <!--div class="field">
                            <label class="label" for="title">Assembla Key</label>

                            <div class="control">
                                <input style="width: 200px;" class="input  @error('assembla_key') is-danger @enderror" type="text" name="assembla_key" id="assembla_key" value="{{ old('assembla_key') }}">

                                <p class="help">Assembla Key with access to selected project</p>
                                @error('assembla_key')
                                <p class="help is-danger">{{ $errors->first('assembla_key') }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="field">
                            <label class="label" for="title">Assembla Secret</label>

                            <div class="control">
                                <input style="width: 200px;" class="input  @error('assembla_secret') is-danger @enderror" type="text" name="assembla_secret" id="assembla_secret" value="{{ old('assembla_secret') }}">

                                <p class="help">Assembla Secret with access to selected project</p>
                                @error('assembla_secret')
                                <p class="help is-danger">{{ $errors->first('assembla_secret') }}</p>
                                @enderror
                            </div>
                        </div -->

                        <div class="field">
                            <label class="label" for="title">From Date</label>

                            <div class="control">
                                <input style="width: 200px;" class="input date  @error('from_date') is-danger @enderror" type="text" name="from_date" id="from_date" value="{{ old('from_date') }}">

                                <p class="help">Starting date for the report</p>
                                @error('from_date')
                                <p class="help is-danger">{{ $errors->first('from_date') }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="field">
                            <label class="label" for="title">To Date</label>

                            <div class="control">
                                <input style="width: 200px;" class="input  @error('to_date') is-danger @enderror" type="text" name="to_date" id="to_date" value="{{ old('to_date') }}">

                                <p class="help">Ending date for the report</p>
                                @error('to_date')
                                <p class="help is-danger">{{ $errors->first('to_date') }}</p>
                                @enderror
                            </div>
                        </div>


                        <div class="field is-grouped">
                            <div class="control">
                                <button class="button is-link" type="submit">Generate Report</button>
                            </div>
                        </div>
                    </form>

                    @if ($results)
                        <div class="console">
                            <header>
                                <p>{{ strtolower(Auth::user()->name) }}@metricsapp</p>
                            </header>
                            <div class="consolebody">
                                @foreach($results as $line)
                                    <p>{{$line}}</p>
                                @endforeach
                            </div>
                        </div>



                    @endif


                </div>
            </div>
        </div>
    </div>
@endsection

<style>


    .console {
        font-family: 'Fira Mono';
        width: 95%;
        height: 450px;
        box-sizing: border-box;
        margin: auto;
    }

    .console header {
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        background-color: #555;
        height: 35px;
        line-height: 35px;
        text-align: left;
        padding-left: 20px;
        color: #DDD;
    }

    .console .consolebody {
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
        box-sizing: border-box;
        padding: 5px 20px;
        height: calc(100% - 40px);
        overflow: scroll;
        background-color: #000;
        color: #63de00;
    }

    .console .consolebody p {
        line-height: 1.5rem;
    }
</style>







