@extends('layouts.report')

@section('content')
<?php
$reportBody = json_decode($report->body);
?>
<div class="report-header">
    <h4 style="margin-left: 17px;">Sprints Report</h4>
</div>
@foreach($reportBody->sprints as $sprintId => $sprint)
<div class="col-12" style="margin-bottom:20px">
    <div class="card shadow mb-12">
        <div class="card-header d-flex align-items-center">

            {{$sprint->project_name}}


            <span class="separator d-none d-md-block"></span>
            <style>
                .separator::after {
                    content: '>';
                    color: gray;
                    margin-right: 5px;
                    margin-left: 5px;
                }

            </style>
            {{$sprint->sprint_name}}

            &nbsp;&nbsp;|&nbsp;&nbsp;<a href="{{$sprint->assembla_url}}" class="font-weight-bold" target="_blank">
                <span class="assembla-link-text">View in Assembla</span> <img width="25" src="https://assets2.assembla.com/assets/favicon/apple-touch-icon-152x152-62313763336639636561616137656631316139363330643166373263366434336331636664333539336231633165396334626630633630383665636661306165.png" alt="Image" style="position: relative; top: -3px;"/></a>

        </div>

        <div class="card-bg-secondary">
            <div class="d-flex">
                <div class="w-33 border-right border-bottom">
                    <div class="p-4">
                        <small class="text-uppercase">Worked Hours</small>
                        <h4 class="mt-4 mb-0">{{$sprint->worked_hours}}</h4>
                    </div>
                </div>
                <div class="w-33 border-right border-bottom">
                    <div class="p-4">
                        <small class="text-uppercase">Remaining Hours</small>
                        <h4 class="mt-4 mb-0">{{$sprint->remaining_hours}}</h4>
                    </div>
                </div>
                <div class="w-33 border-bottom">
                    <div class="p-4">
                        <small class="text-uppercase">Stories</small>
                        <h4 class="mt-4 mb-0">{{$sprint->stories}}</h4>
                        <span class="text-danger">{{ $sprintOpenTotalStories = ($sprint->stories - $sprint->completed_stories) }} Open ({{Helper::getPercentageValue($sprintOpenTotalStories, $sprint->stories)}}%)</span>
                        <span class="text-success">{{ $sprint->completed_stories }} Closed ({{$sprint->completed_stories_percentage}}%)</span>

                    </div>
                </div>
            </div>
            <div class="d-flex">
                <div class="w-33 border-right">
                    <div class="p-4 mb-0">
                        <small class="text-uppercase">Remaining Estimates</small>
                        <h4 class="mt-4">{{$sprint->remaining_estimate}} ({{$sprint->remaining_estimate_percentage}}%)</h4>
                    </div>
                </div>
                <div class="w-33 border-right">
                    <div class="p-4 mb-0">
                        <small class="text-uppercase">Completed Estimates</small>
                        <h4 class="mt-4">{{$sprint->completed_estimate}} ({{$sprint->completed_estimate_percentage}}%)</h4>
                    </div>
                </div>
                <div class="w-33">
                    <div class="p-4 mb-0">
                        <small class="text-uppercase">Total Estimates</small>
                        <h4 class="mt-4">{{$sprint->total_estimate}}</h4></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

@if(property_exists($reportBody, 'total'))
    <div class="col-12" style="margin-bottom:20px">
        <div class="card shadow mb-12">
            <div class="card-header d-flex align-items-center">Total</div>

            <div class="card-bg-secondary">
                <div class="d-flex">
                    <div class="w-33 border-right border-bottom">
                        <div class="p-4">
                            <small class="text-uppercase">Total Worked Hours</small>
                            <h4 class="mt-4 mb-0">{{$reportBody->total->worked_hours}}</h4>
                        </div>
                    </div>
                    <div class="w-33 border-right border-bottom">
                        <div class="p-4">
                            <small class="text-uppercase">Total Remaining Hours</small>
                            <h4 class="mt-4 mb-0">{{$reportBody->total->remaining_hours}}</h4>
                        </div>
                    </div>
                    <div class="w-33 border-bottom">
                        <div class="p-4">
                            <small class="text-uppercase">Total Stories</small>
                            <h4 class="mt-4 mb-0">{{$reportBody->total->stories}}</h4>
                            <span class="text-danger">{{ $OpenTotalStories = ($reportBody->total->stories - $reportBody->total->completed_stories) }} Open ({{Helper::getPercentageValue($OpenTotalStories, $reportBody->total->stories)}}%)</span>
                            <span class="text-success">{{ $reportBody->total->completed_stories }} Closed ({{$reportBody->total->completed_stories_percentage}}%)</span>

                        </div>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="w-33 border-right">
                        <div class="p-4 mb-0">
                            <small class="text-uppercase">Total Remaining Estimates</small>
                            <h4 class="mt-4">{{$reportBody->total->remaining_estimate}} ({{$reportBody->total->remaining_estimate_percentage}}%)</h4>
                        </div>
                    </div>
                    <div class="w-33 border-right">
                        <div class="p-4 mb-0">
                            <small class="text-uppercase">Completed Estimates</small>
                            <h4 class="mt-4">{{$reportBody->total->completed_estimate}} ({{$reportBody->total->completed_estimate_percentage}}%)</h4>
                        </div>
                    </div>
                    <div class="w-33">
                        <div class="p-4 mb-0">
                            <small class="text-uppercase">Total Estimates</small>
                            <h4 class="mt-4">{{$reportBody->total->total_estimate}}</h4></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
<style>
    .w-33 {
        width: 34% !important;
    }
</style>

@endsection


