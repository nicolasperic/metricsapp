@extends('layouts.app')

@section('container-title', ' Current Milestones')

@section('breadcrumbs',  Breadcrumbs::render('sprints'))

@section('actions')
    <a href="{{ route('sprints.sync-all-current-sprints') }}" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> Sync All Current Milestones</a>
@endsection

@section('content')


    <div class="current-sprints">
        <div class="row justify-content-center">

            <?php $totalEstimate = 0;$totalRemainingEstimate = 0;$totalCompletedEstimate = 0; $totalWorkedHours = 0; $totalRemainingHours = 0; $totalStories = 0; $totalCompletedStories = 0; $totalOpenStories = 0; $olderUpdatedAt = false;?>
            @forelse ($currentSprints as $sprint)
            <?php
                    /** @var \App\Models\Metrics\Sprint\TicketsMetrics $ticketsMetrics */
                    $ticketsMetrics = $sprint->getTicketsMetricsInstance();
            ?>
            <div class="col-12" style="margin-bottom:20px">
                <div>
                    <div class="card shadow mb-12">
                        <div class="card-header d-flex align-items-center">
                            @foreach ($sprint->projects as $project)
                            <a href="{{ route('projects.show',$project->wikiname) }}" class="font-weight-bold d-none d-md-block">{{$project->name}}</a>
                            <a href="{{ route('projects.show',$project->wikiname) }}" class="project-link font-weight-bold d-block d-md-none">{{$project->code}}</a>
                            @endforeach
                            <span class="separator d-none d-md-block"></span>
                            <style>
                                .separator::after {
                                    content: '>';
                                    color: gray;
                                    margin-right: 5px;
                                    margin-left: 5px;
                                }

                            </style>
                            <a href="{{ route('sprints.show', [$project->wikiname, $sprint->sprint_assembla_id]) }}" class="font-weight-bold">{{ $sprint->name}}</a>
                                &nbsp;<?= $sprint->getFormattedPlannerType()?>
                            &nbsp;&nbsp;|&nbsp;&nbsp;<a href="https://app.assembla.com/spaces/{{ $project->wikiname }}/milestones/{{ $sprint->sprint_assembla_id }}" class="font-weight-bold" target="_blank">
                                    <span class="assembla-link-text">View in Assembla</span> <img width="25" src="https://assets2.assembla.com/assets/favicon/apple-touch-icon-152x152-62313763336639636561616137656631316139363330643166373263366434336331636664333539336231633165396334626630633630383665636661306165.png" alt="Image" style="position: relative; top: -3px;"/></a>
                            &nbsp;&nbsp;|&nbsp;&nbsp;<i class="last-sync d-none d-md-block">Last synced on {{$sprint->updated_at }}</i>
                        </div>
                        <?php $olderUpdatedAt = ($olderUpdatedAt === false || $sprint->updated_at < $olderUpdatedAt)?$sprint->updated_at: $olderUpdatedAt;?>
                        <div class="card-bg-secondary">
                            <div class="d-flex">
                                <div class="w-33 border-right border-bottom">
                                    <div class="p-4">
                                        <small class="text-uppercase">Worked Hours</small>
                                        <h4 class="mt-4 mb-0">{{ $sprintTotalWorkedHours = $ticketsMetrics->getTotalWorkedHours() }}</h4>
                                    </div>
                                </div>
                                <div class="w-33 border-right border-bottom">
                                    <div class="p-4">
                                        <small class="text-uppercase">Remaining Hours</small>
                                        <h4 class="mt-4 mb-0">{{ $sprintTotalRemainingHours = $ticketsMetrics->getTotalWorkingHours() }}</h4>
                                    </div>
                                </div>
                                <div class="w-33 border-bottom">
                                    <div class="p-4">
                                        <small class="text-uppercase">Stories</small>
                                        <h4 class="mt-4 mb-0">{{ $sprintTotalStories = $ticketsMetrics->getStoriesCount() }}</h4>
                                        <?php $sprintTotalCompletedStories = $ticketsMetrics->getCompletedStoriesCount();?>
                                        <span class="text-danger">{{ $sprintOpenTotalStories = ($sprintTotalStories - $sprintTotalCompletedStories) }} Open ({{Helper::getPercentageValue($sprintOpenTotalStories, $sprintTotalStories)}}%)</span>
                                        <span class="text-success">{{ $sprintTotalCompletedStories }} Closed ({{Helper::getPercentageValue($sprintTotalCompletedStories, $sprintTotalStories)}}%)</span>

                                    </div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="w-33 border-right">
                                    <div class="p-4 mb-0">
                                        <small class="text-uppercase">Remaining Estimates</small>
                                        <h4 class="mt-4">{{ $sprintRemainingEstimate = $ticketsMetrics->getTotalRemainingEstimate() }} ({{ ($ticketsMetrics->getTotalCompletedEstimatePercentage() != 0)?100 - $ticketsMetrics->getTotalCompletedEstimatePercentage():0 }}%)</h4>
                                    </div>
                                </div>
                                <div class="w-33 border-right">
                                    <div class="p-4 mb-0">
                                        <small class="text-uppercase">Completed Estimates</small>
                                        <h4 class="mt-4">{{ $sprintCompletedEstimate = $ticketsMetrics->getTotalCompletedEstimate() }} ({{ $ticketsMetrics->getTotalCompletedEstimatePercentage() }}%)</h4>
                                    </div>
                                </div>
                                <div class="w-33">
                                    <div class="p-4 mb-0">
                                        <small class="text-uppercase">Total Estimates</small>
                                        <h4 class="mt-4">{{ $sprintTotalEstimate = $ticketsMetrics->getTotalEstimate() }}</h4></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!----> </div>
            </div>

            <?php
                $totalRemainingEstimate += $sprintRemainingEstimate;
                $totalCompletedEstimate += $sprintCompletedEstimate;
                $totalEstimate          += $sprintTotalEstimate;
                $totalWorkedHours       += $sprintTotalWorkedHours;
                $totalRemainingHours    += $sprintTotalRemainingHours;
                $totalStories           += $sprintTotalStories;
                $totalCompletedStories  += $sprintTotalCompletedStories;
                $totalOpenStories       += $sprintOpenTotalStories;
            ?>
            @empty
                <p>No currents sprints created yet</p>
            @endforelse

            @if(count($currentSprints) > 1)
                <?php $totalCompletedEstimatePercentage = ($totalEstimate != 0)? number_format($totalCompletedEstimate/$totalEstimate*100,2):0?>
                <div class="col-12" style="margin-bottom:20px">
                    <div>
                        <div class="card shadow mb-12">
                            <div class="card-header d-flex align-items-center ">
                                <h6 class="m-0 font-weight-bold text-primary">Total</h6>
                                &nbsp;&nbsp;&nbsp;&nbsp;<i class="last-sync">Oldest synced milestone on <?= $olderUpdatedAt?> ({{Helper::getTimeDiff($olderUpdatedAt)}})</i>
                            </div>
                            <div class="card-bg-secondary">
                                <div class="d-flex">
                                    <div class="w-33 border-right border-bottom">
                                        <div class="p-4">
                                            <small class="text-uppercase">Total Worked Hours</small>
                                            <h4 class="mt-4 mb-0">{{ $totalWorkedHours }}</h4>
                                        </div>
                                    </div>
                                    <div class="w-33 border-right border-bottom">
                                        <div class="p-4">
                                            <small class="text-uppercase">Total Remaining Hours</small>
                                            <h4 class="mt-4 mb-0">{{ $totalRemainingHours }}</h4>
                                        </div>
                                    </div>
                                    <div class="w-33 border-bottom">
                                        <div class="p-4">
                                            <small class="text-uppercase">Total Stories</small>
                                            <h4 class="mt-4 mb-0">{{ $totalStories }}</h4>

                                            <span class="text-danger">{{ $totalOpenStories }} Open ({{Helper::getPercentageValue($totalOpenStories, $totalStories)}}%)</span>
                                            <span class="text-success">{{ $totalCompletedStories }} Closed ({{Helper::getPercentageValue($totalCompletedStories, $totalStories)}}%)</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="w-33 border-right">
                                        <div class="p-4 mb-0">
                                            <small class="text-uppercase">Total Remaining Estimates</small>
                                            <h4 class="mt-4">{{ $totalRemainingEstimate }} ({{ 100 - $totalCompletedEstimatePercentage }}%)</h4>
                                        </div>
                                    </div>
                                    <div class="w-33 border-right">
                                        <div class="p-4 mb-0">
                                            <small class="text-uppercase">Total Completed Estimates</small>
                                            <h4 class="mt-4">{{ $totalCompletedEstimate }} ({{ $totalCompletedEstimatePercentage }}%)</h4>
                                        </div>
                                    </div>
                                    <div class="w-33">
                                        <div class="p-4 mb-0">
                                            <small class="text-uppercase">Total Estimates</small>
                                            <h4 class="mt-4">{{ $totalEstimate }}</h4></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!----> </div>
                </div>
            @endif


        </div>
    </div>
@endsection








