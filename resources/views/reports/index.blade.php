@extends('layouts.app')
@section('head')
    <!--link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script-->


    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/css/bootstrap-select.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.7.5/js/bootstrap-select.min.js"></script>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-12">
        @if (count($reports))
            <p class="help">Only displaying reports from the last 7 days</p>
            <table class="table table-striped">
                <thead>
                <th>Status</th>
                <th>Report Type</th>
                <th>Data</th>
                <th>Created at</th>
                <th>View Report</th>
                </thead>
                <tbody>
                @foreach($reports as $report)
                    <tr id="report-{{$report->id}}">
                        <td class="report-status">{{ $report->getStatusLabel() }}</td>
                        <td>{{ $report->title }}</td>
                        <td>{{ $report->getRequestDataFormatted() }}</td>
                        <td>{{ $report->created_at}}</td>
                        <td class="report-link">@if ($report->isProcessed())<a href="{{url("reports/{$report->id}")}}" target="_blank">View</a> @endif</td>
                    </tr>
                @endforeach
                </tbody>
            </table>


        @endif
    </div>
    <div class="col-md-12">
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

        <div class="card shadow mb-12">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Tracked Time by User Story Report</h6>
            </div>
            <form method="POST" action="{{url("reports/hoursByUs")}}" style="padding: 20px;">
                @csrf


                <div class="card-bg-secondary pl-4">
                    <div class="shortcuts small ">
                        <a href="#" class="set-hours-us-date-links" data-from="{{ Helper::getLastWeekMonday() }}" data-to="{{ Helper::getLastWeekSunday() }}">Last week</a>
                        <a href="#" class="set-hours-us-date-links" data-from="{{ Helper::getLastMonthFirstDay() }}" data-to="{{ Helper::getLastMonthLastDay() }}" >Last month</a>
                        <a href="#" class="set-hours-us-date-links" data-from="{{ Helper::getThisWeekMonday() }}" data-to="{{ Helper::getThisWeekSunday() }}" >Current week</a>
                        <a href="#" class="set-hours-us-date-links" data-from="{{ Helper::getThisMonthFirstDay() }}" data-to="{{ Helper::getThisMonthLastDay() }}" >Current month</a>
                    </div>

                    <div class="d-flex project-report-fields">
                        <div class="w-33">
                            <div class="p-1">

                                <small class="text-uppercase">Space</small>
                                <div class="control">
                                    @if (count($projects))
                                        <select name="project" id="project" class="select picker" data-size="10" data-live-search="true">
                                            @foreach($projects as $project)
                                                <option value="{{ $project->project_assembla_id }}" @if($project->project_assembla_id == old('project')) selected @endif>{{ $project->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('project')
                                        <p class="help is-danger">{{ $errors->first('project') }}</p>
                                        @enderror
                                    @else
                                        No spaces yet, <a href="{{ route('projects.sync') }}">Import spaces</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="w-33">
                            <div class="p-1">
                                <small class="text-uppercase">From Date</small>
                                <div class="control">
                                    <input class="input date @error('hours_us_from_date') is-danger @enderror" type="text" name="hours_us_from_date" id="hours_us_from_date" value="{{ old('hours_us_from_date') }}" readonly="readonly">

                                    <p class="help">Starting date for the report</p>
                                    @error('hours_us_from_date')
                                    <p class="help is-danger">{{ $errors->first('hours_us_from_date') }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="w-33">
                            <div class="p-1">
                                <small class="text-uppercase">To Date</small>
                                <div class="control">
                                    <input class="input date @error('hours_us_to_date') is-danger @enderror" type="text" name="hours_us_to_date" id="hours_us_to_date" value="{{ old('hours_us_to_date') }}" readonly="readonly">

                                    <p class="help">Ending date for the report</p>
                                    @error('hours_us_to_date')
                                    <p class="help is-danger">{{ $errors->first('hours_us_to_date') }}</p>
                                    @enderror
                                </div>


                            </div>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="w-33">
                            <button class="btn btn-primary" type="submit">Generate Report</button>
                        </div>
                    </div>
                </div>

                <style>
                    .w-33 {
                        width: 34% !important;
                    }
                    .set-hours-us-date-links, .set-hours-user-date-links {
                        font-size: .75rem;
                        margin-rigt: 5px;
                    }
                </style>


                <!--div class="field">
                    <label class="label" for="project">Project</label>


                    <div class="control">
                        @if (count($projects))
                            <select name="project" id="project" class="selectpicker" data-size="10" data-live-search="true">
                                @foreach($projects as $project)
                                    <option value="{{ $project->project_assembla_id }}" @if($project->project_assembla_id == old('project')) selected @endif>{{ $project->name }}</option>
                                @endforeach
                            </select>
                            @error('project')
                            <p class="help is-danger">{{ $errors->first('project') }}</p>
                            @enderror
                        @else
                            No projects yet, <a href="{{ route('projects.sync') }}">Import Projects</a>
                        @endif

                    </div>
                </div>
                <a href="#" class="set-hours-us-date-links" data-from="{{ Helper::getLastWeekMonday() }}" data-to="{{ Helper::getLastWeekSunday() }}">Last week</a>
                <a href="#" class="set-hours-us-date-links" data-from="{{ Helper::getLastMonthFirstDay() }}" data-to="{{ Helper::getLastMonthLastDay() }}" >Last month</a>
                <a href="#" class="set-hours-us-date-links" data-from="{{ Helper::getThisWeekMonday() }}" data-to="{{ Helper::getThisWeekSunday() }}" >Current week</a>
                <a href="#" class="set-hours-us-date-links" data-from="{{ Helper::getThisMonthFirstDay() }}" data-to="{{ Helper::getThisMonthLastDay() }}" >Current month</a>

                <div class="field">
                    <label class="label" for="hours_us_from_date">From Date</label>

                    <div class="control">
                        <input style="width: 200px;" class="input date @error('hours_us_from_date') is-danger @enderror" type="text" name="hours_us_from_date" id="hours_us_from_date" value="{{ old('hours_us_from_date') }}" readonly="readonly">

                        <p class="help">Starting date for the report</p>
                        @error('hours_us_from_date')
                        <p class="help is-danger">{{ $errors->first('hours_us_from_date') }}</p>
                        @enderror
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="hours_us_to_date">To Date</label>

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
                        <button class="btn btn-primary" type="submit">Generate Report</button>
                    </div>
                </div-->
            </form>
        </div>


        <div class="card shadow mt-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Tracked Time by Users/Spaces Report</h6>
            </div>
            <form method="POST" action="{{url("reports/hoursByUser")}}" style="padding: 20px 20px 20px 40px;">
                @csrf

                <div class="field">
                    <label class="label" for="title">Spaces</label>


                    <div class="control">
                        @if (count($projects))
                            <select name="projects[]"  id="projects" multiple class="select picker" data-size="10"  data-live-search="true">
                                @foreach($projects as $project)
                                    <option shared="{{ $project->shared }}" value="{{ $project->project_assembla_id }}" @if($project->project_assembla_id == old('projects')) selected @endif data-wikiname="{{ $project->wikiname }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                            @error('projects')
                            <p class="help is-danger">{{ $errors->first('projects') }}</p>
                            @enderror
                        @else
                            No spaces yet, <a href="{{ route('projects.sync') }}">Import Spaces</a>
                        @endif
                    </div>
                </div>

                <div class="field mt-2 mb-2" id="users-field" style="display: none;">
                    <label class="label d-block" for="title">Users</label>
                    <small>You've selected a shared space, filter the tracked time by selecting your team. Only shared spaces will be filtered, it's not required to select any users.</small>

                    <div class="control">
                        @if (count($users))
                            <select name="users[]" id="users" multiple class="select picker" data-size="10"  data-live-search="true">
                                @foreach($users as $userAssemblaId => $userName)
                                    <option value="{{ $userAssemblaId }}" @if($userAssemblaId == old('users')) selected @endif>{{ $userName }}</option>
                                @endforeach
                            </select>
                            @error('users')
                            <p class="help is-danger">{{ $errors->first('users') }}</p>
                            @enderror
                        @else
                            No users yet, users can be imported by space from each space page</a>
                        @endif
                    </div>
                </div>

                <a href="#" class="set-hours-user-date-links" data-from="{{ Helper::getLastWeekMonday() }}" data-to="{{ Helper::getLastWeekSunday() }}">Last week</a>
                <a href="#" class="set-hours-user-date-links" data-from="{{ Helper::getLastMonthFirstDay() }}" data-to="{{ Helper::getLastMonthLastDay() }}" >Last month</a>
                <a href="#" class="set-hours-user-date-links" data-from="{{ Helper::getThisWeekMonday() }}" data-to="{{ Helper::getThisWeekSunday() }}" >Current week</a>
                <a href="#" class="set-hours-user-date-links" data-from="{{ Helper::getThisMonthFirstDay() }}" data-to="{{ Helper::getThisMonthLastDay() }}" >Current month</a>
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
                        <button class="btn btn-primary" type="submit">Generate Report</button>
                    </div>
                </div>
            </form>
        </div>

            <div class="card shadow mt-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Milestones Report</h6>
                </div>
                <form method="POST" action="{{url("reports/generateSprintsReport")}}" style="padding: 20px 20px 20px 40px;">
                    @csrf

                    <div class="field">
                        <label class="label" for="title">Milestones</label>


                        @if (count($currentSprints))
                        <div class="shortcuts small">
                            <a href="#" class="set-current-sprints-link">Set Current Milestones</a>
                        </div>
                        @endif

                        <div class="control">
                            @if (count($sprints))
                                <select name="sprints[]"  id="sprints" class="select picker" data-size="10" multiple data-live-search="true">
                                    @foreach($sprints as $sprint)
                                        <option value="{{ $sprint['sprint_assembla_id'] }}" @if($sprint['sprint_assembla_id'] == old('sprints')) selected @endif>{{ $sprint['project_name'] .' > ' . $sprint['name'] }}</option>
                                    @endforeach
                                </select>

                                <p class="help">A maximum of 12 milestones can be selected</p>
                                @error('sprints')
                                <p class="help is-danger">{{ $errors->first('sprints') }}</p>
                                @enderror
                            @else
                                No milestones yet, <a href="{{ route('projects.sync') }}">Import Spaces</a> and then import milestones from a space page
                            @endif
                        </div>
                        </div>



                    <div class="field is-grouped">
                        <div class="control">
                            <button class="btn btn-primary" type="submit">Generate Report</button>
                        </div>
                    </div>
                </form>
            </div>
    </div>
</div>


    <script type="text/javascript">
        //$('.selectpicker').selectpicker();
        $('.date').datepicker({
            format: 'yyyy/mm/dd',
            autoclose: true
        });

        $('.set-hours-us-date-links').click(function(e) {
            $('#hours_us_from_date').val($(this).data('from'));
            $('#hours_us_to_date').val($(this).data('to'));
            e.preventDefault();
        });

        $('.set-hours-user-date-links').click(function(e) {
            $('#hours_user_from_date').val($(this).data('from'));
            $('#hours_user_to_date').val($(this).data('to'));
            e.preventDefault();
        });

        $('.set-current-sprints-link').click(function(e) {
            var currentSprints = {!! json_encode($currentSprints) !!}

            currentSprints.forEach(function(sprintId) {
                $('#sprints option[value='+sprintId+']').attr('selected', true);
            });

            e.preventDefault();
        });


        let shared = 0;
        $('#projects').change(function () {

            shared = 0;
            $('#projects > option:selected').each(function() {
                if ($(this).attr('shared') == 1) {
                    shared = 1;
                }
            });
            if (shared) {
                $('#users-field').show();
            } else {
                $('#users-field').hide();
            }
        });



    </script>
@endsection

<style>
<?php //TODO move this styles to a CSS or SCSS file?>

    .project-report-fields .control select{
        max-width: 94%;
        height: 30px;
    }
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

    #users, #projects, #sprints {
        height: 200px;
    }

    .datepicker-switch {
        color: white;
    }

    .datepicker.datepicker-dropdown.dropdown-menu {
        z-index: 10000 !important;
    }

    .btn.dropdown-toggle.bs-placeholder.btn-light, .btn.dropdown-toggle.btn-light {
        height: 35px !important;
    }

    .scroll-to-top i {
        position: absolute !important;
        top: 14px !important;
        left: 17px !important;
    }
</style>







