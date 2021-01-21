@extends('layouts.report')

@section('content')
<?php
    $reportBody = json_decode($report->body);
    $header = $reportBody->header;
    $userStories = $reportBody->user_stories;
    $noUserStories = $reportBody->no_user_stories;
    $withoutTicket = $reportBody->without_ticket;
    $types = $reportBody->types;
    $footer = $reportBody->footer;
    $totalHours = $header->total_hours;
?>

<div class="report-header">
    <h4>{{$header->wikiname}} | Hours By User Story Report</h4>

    <div class="report-dates-totals">
        <span>From <strong>{{$header->from}}</strong> to <strong>{{$header->to}}</strong></span>
        <span>Total Hours {{$header->total_hours}} | Total Tasks {{$header->total_tasks}}</span>
    </div>

</div>

<div class="report-table-header">
    <h6>User Stories</h6>
    <span>Total hours: {{$userStories->total_hours}} ({{ Helper::getPercentageValue($userStories->total_hours, $totalHours, $decimals = 2) }}%)</span>
</div>
<div class="report-user-stories">
    <table class="table table-striped">
        <thead>
        <tr>
            @foreach($userStories->header_columns as $id => $column)
                <th scope="col">{{$column}}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($userStories->tickets as $id => $userStory)
            <tr>
                <td>{{$userStory->description}}</td>
                <td>{{$userStory->total_invested_hours}}</td>
                <td>{{$userStory->hours}}</td>
                <td>{{$userStory->tasks}}</td>
                <td>{{$userStory->status}}</td>
                <td>{{$userStory->type}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>



@if($noUserStories->total_hours)
    <div class="report-table-header">
        <h6>Tasks (not a User Story)</h6>
        <span>Total hours: {{$noUserStories->total_hours}} ({{ Helper::getPercentageValue($noUserStories->total_hours, $totalHours, $decimals = 2) }}%)</span>
    </div>
    <div class="report-no-user-stories">
        <table class="table table-striped">
            <thead>
            <tr>
                @foreach($noUserStories->header_columns as $id => $column)
                    <th scope="col">{{$column}}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($noUserStories->tickets as $id => $noUserStory)
                <tr>
                    <td>{{$noUserStory->description}}</td>
                    <td>{{$noUserStory->total_invested_hours}}</td>
                    <td>{{$noUserStory->hours}}</td>
                    <td>{{$noUserStory->tasks}}</td>
                    <td>{{$noUserStory->status}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif


@if($withoutTicket->total_hours)
    <div class="report-table-header">
        <h6>Tracked time without ticket</h6>
        <span>Total hours: {{$withoutTicket->total_hours}} ({{ Helper::getPercentageValue($withoutTicket->total_hours, $totalHours, $decimals = 2) }}%)</span>
    </div>
    <div class="report-tracked-without-ticket">
        <table class="table table-striped">
            <thead>
            <tr>
                @foreach($withoutTicket->header_columns as $id => $column)
                    <th scope="col">{{$column}}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($withoutTicket->users as $id => $user)
                <tr>
                    <td>{{$user->username}}</td>
                    <td>{{$user->hours}}</td>
                    <td>{{$user->tasks}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif

@foreach($types as $typeLabel => $typeData)
    <div class="type">
        <span class="type-label">{{$typeLabel}}</span>

        {{$typeData->type_count}} user stories ({{$typeData->type_count_percentage}}%)
        {{$typeData->type_hours}} hours ({{$typeData->type_hours_percentage}}%)
    </div>

@endforeach


<div style="display: none;">{{$footer->total_api_calls}} total api calls | {{$footer->execution_time}} minutes</div>

@endsection


