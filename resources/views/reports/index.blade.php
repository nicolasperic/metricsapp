@extends('layouts.appbis')
@section('head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.5/css/bulma.css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.css" rel="stylesheet">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">

                @if ($results)
                    <div class="console">
                        <header>
                            <p>{{ str_replace(' ','',strtolower(Auth::user()->name)) }}@metricsapp</p>
                        </header>
                        <div class="consolebody">
                            @foreach($results as $line)
                                <p>{{$line}}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">Hours by User Story Report</div>
                    <form method="POST" action="/reports/hoursByUs" style="padding: 20px;">
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

                        <div class="field">
                            <label class="label" for="title">From Date</label>

                            <div class="control">
                                <input style="width: 200px;" class="input date @error('hours_us_from_date') is-danger @enderror" type="text" name="hours_us_from_date" id="hours_us_from_date" value="{{ old('hours_us_from_date') }}" readonly="readonly">

                                <p class="help">Starting date for the report</p>
                                @error('hours_us_from_date')
                                <p class="help is-danger">{{ $errors->first('hours_us_from_date') }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="field">
                            <label class="label" for="title">To Date</label>

                            <div class="control">
                                <input style="width: 200px;" class="input date @error('hours_us_to_date') is-danger @enderror" type="text" name="hours_us_to_date" id="hours_us_to_date" value="{{ old('hours_us_to_date') }}" readonly="readonly">

                                <p class="help">Ending date for the report</p>
                                @error('hours_us_to_date')
                                <p class="help is-danger">{{ $errors->first('hours_us_to_date') }}</p>
                                @enderror
                            </div>
                        </div>


                        <div class="field is-grouped">
                            <div class="control">
                                <button class="button is-link" type="submit">Generate Report</button>
                            </div>
                        </div>
                    </form>
                </div>


                <div class="card" style="margin-top: 20px;">
                    <div class="card-header">Hours by Users Report</div>
                    <form method="POST" action="/reports/hoursByUser" style="padding: 20px;">
                        @csrf

                        <div class="field">
                            <label class="label" for="title">Projects</label>


                            <div class="control">
                                @if (count($projects))
                                    <select name="projects[]"  id="projects" multiple>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->project_assembla_id }}" @if($project->project_assembla_id == old('projects')) selected @endif data-wikiname="{{ $project->wikiname }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('projects')
                                    <p class="help is-danger">{{ $errors->first('projects') }}</p>
                                    @enderror
                                @else
                                    No projects yet, <a href="{{url("projects/importProjects")}}">Import Projects</a>
                                @endif
                            </div>
                        </div>

                        <div class="field">
                            <label class="label" for="title">Users</label>


                            <div class="control">
                                @if (count($users))
                                    <select name="users[]" id="users" multiple>
                                        @foreach($users as $userAssemblaId => $userName)
                                            <option value="{{ $userAssemblaId }}" @if($userAssemblaId == old('users')) selected @endif>{{ $userName }}</option>
                                        @endforeach
                                    </select>
                                    @error('users')
                                    <p class="help is-danger">{{ $errors->first('users') }}</p>
                                    @enderror
                                @else
                                    No users yet, users can be imported by space from each project page</a>
                                @endif
                            </div>
                        </div>

                        <div class="field">
                            <label class="label" for="title">From Date</label>

                            <div class="control">
                                <input style="width: 200px;" class="input date @error('hours_user_from_date') is-danger @enderror" type="text" name="hours_user_from_date" id="hours_user_from_date" value="{{ old('hours_user_from_date') }}" readonly="readonly">

                                <p class="help">Starting date for the report</p>
                                @error('hours_user_from_date')
                                <p class="help is-danger">{{ $errors->first('hours_user_from_date') }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="field">
                            <label class="label" for="title">To Date</label>

                            <div class="control">
                                <input style="width: 200px;" class="input date @error('hours_user_to_date') is-danger @enderror" type="text" name="hours_user_to_date" id="hours_user_to_date" value="{{ old('hours_user_to_date') }}" readonly="readonly">

                                <p class="help">Ending date for the report</p>
                                @error('hours_user_to_date')
                                <p class="help is-danger">{{ $errors->first('hours_user_to_date') }}</p>
                                @enderror
                            </div>
                        </div>


                        <div class="field is-grouped">
                            <div class="control">
                                <button class="button is-link" type="submit">Generate Report</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $('.date').datepicker({
            format: 'yyyy/mm/dd',
            autoclose: true
        });
    </script>
@endsection

<style>
<?php //TODO move this styles to a CSS or SCSS file?>

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
        color: #62ff00;
    }

    .console .consolebody p {
        line-height: 1.5rem;
        min-height: 1.5rem;
    }

    /*Bootstrap Calendar*/
    .datepicker {
        border-radius: 0;
        padding: 0;
    }
    .datepicker-days table thead, .datepicker-days table tbody, .datepicker-days table tfoot {
        padding: 10px;
        display: list-item;
    }
    .datepicker-days table thead, .datepicker-months table thead, .datepicker-years table thead, .datepicker-decades table thead, .datepicker-centuries table thead {
        background: #3273dc;
        color: #ffffff !important;
        border-radius: 0;
    }
    .datepicker-days table thead tr:nth-child(2n+0) td, .datepicker-days table thead tr:nth-child(2n+0) th {
        border-radius: 3px;
    }
    .datepicker-days table thead tr:nth-child(3n+0) {
        text-transform: uppercase;
        font-weight: 300 !important;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.7);
    }
    .table-condensed > tbody > tr > td, .table-condensed > tbody > tr > th, .table-condensed > tfoot > tr > td, .table-condensed > tfoot > tr > th, .table-condensed > thead > tr > td, .table-condensed > thead > tr > th {
        padding: 11px 13px;
    }
    .datepicker-months table thead td, .datepicker-months table thead th, .datepicker-years table thead td, .datepicker-years table thead th, .datepicker-decades table thead td, .datepicker-decades table thead th, .datepicker-centuries table thead td, .datepicker-centuries table thead th {
        border-radius: 0;
    }
    .datepicker td, .datepicker th {
        border-radius: 50%;
        padding: 0 12px;
    }
    .datepicker-days table thead, .datepicker-months table thead, .datepicker-years table thead, .datepicker-decades table thead, .datepicker-centuries table thead {
        background: #3273dc;
        color: #ffffff !important;
        border-radius: 0;
    }
    .datepicker table tr td.active, .datepicker table tr td.active:hover, .datepicker table tr td.active.disabled, .datepicker table tr td.active.disabled:hover {
        background-image: none;
    }
    .datepicker .prev, .datepicker .next {
        color: rgba(255, 255, 255, 0.5);
        transition: 0.3s;
        width: 37px;
        height: 37px;
    }
    .datepicker .prev:hover, .datepicker .next:hover {
        background: transparent;
        color: rgba(255, 255, 255, 0.99);
        font-size: 21px;
    }
    .datepicker .datepicker-switch {
        font-size: 24px;
        font-weight: 400;
        transition: 0.3s;
    }
    .datepicker .datepicker-switch:hover {
        color: rgba(255, 255, 255, 0.7);
        background: transparent;
    }
    .datepicker table tr td span {
        border-radius: 2px;
        margin: 3%;
        width: 27%;
    }
    .datepicker table tr td span.active, .datepicker table tr td span.active:hover, .datepicker table tr td span.active.disabled, .datepicker table tr td span.active.disabled:hover {
        background-color: #3273dc;/*#3546b3*/
        background-image: none;
    }
    .dropdown-menu {
        border: 1px solid rgba(0,0,0,.1);
        box-shadow: 0 6px 12px rgba(0,0,0,.175);
    }
    .datepicker-dropdown.datepicker-orient-top:before {
        border-top: 7px solid rgba(0,0,0,.1);
    }

    #users, #projects {
        height: 200px;
    }

    .datepicker-switch {
        color: white;
    }


</style>







