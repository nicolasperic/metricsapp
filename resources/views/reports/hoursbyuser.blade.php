@extends('layouts.report')

@section('content')
<?php
$reportBody = json_decode($report->body);
$header = $reportBody->header;
$projects = $reportBody->projects;
$users = $reportBody->users;
?>
<div class="report-header">
    <h4>Hours by Users and Spaces Report</h4>

    <div class="report-dates-totals">
        <span>From <strong>{{ Helper::getDateWithoutHours($header->from) }}</strong> to <strong>{{ Helper::getDateWithoutHours($header->to) }}</strong></span>
    </div>

</div>
<div class="report-stats">
    <div class="text-xs font-weight-bold text-uppercase mb-1">Total Hours</div>
    <div class="h5 mb-0 font-weight-bold text-gray-800">{{$header->total_hours}}</div>
</div>
<div class="report-stats">
    <div class="text-xs font-weight-bold text-uppercase mb-1">Total Tasks</div>
    <div class="h5 mb-0 font-weight-bold text-gray-800">{{$header->total_tasks}}</div>
</div>


<h6>Hours by Spaces</h6>
@foreach($projects as $project)
    <div class="report">
        <div class="report-table-header">
            <h6>{{$project->wikiname}}</h6>
            <span>Total hours: {{$project->total_hours}} ({{ Helper::getPercentageValue($project->total_hours, $header->total_hours, $decimals = 2) }}%)</span>
            <span>Total tasks: {{$project->total_tasks}} ({{ Helper::getPercentageValue($project->total_tasks, $header->total_tasks, $decimals = 2) }}%)</span>
        </div>
        <div class="report-user-stories">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th scope="col" class="w-50">User</th>
                    <th scope="col" class="w-25">Hours</th>
                    <th scope="col" class="w-25">Tasks</th>
                </tr>
                </thead>
                <tbody>

                @foreach($project->users as $user)
                    <tr>
                        <td>{{$user->username}}</td>
                        <td>{{$user->total_hours}} hours ({{ Helper::getPercentageValue($user->total_hours, $project->total_hours, $decimals = 2) }}%)</td>
                        <td>{{$user->total_tasks}} tasks ({{ Helper::getPercentageValue($user->total_tasks, $project->total_tasks, $decimals = 2) }}%)</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endforeach

<div class="report">
    <div class="report-table-header">
        <h6>Hours by User</h6>

    </div>
    <div class="report-user-stories">
        <table class="table table-striped">
            <thead>
            <tr>
                <th scope="col" class="w-50">User</th>
                <th scope="col" class="w-25">Hours</th>
                <th scope="col" class="w-25">Tasks</th>
            </tr>
            </thead>
            <tbody>

            @foreach($users as $user)
                <tr>
                    <td>{{$user->username}}</td>
                    <td>{{$user->total_hours}} hours ({{ Helper::getPercentageValue($user->total_hours, $header->total_hours, $decimals = 2) }}%)</td>
                    <td>{{$user->total_tasks}} tasks ({{ Helper::getPercentageValue($user->total_tasks, $header->total_tasks, $decimals = 2) }}%)</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>


@endsection


