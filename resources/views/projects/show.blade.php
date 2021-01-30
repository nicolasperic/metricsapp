@extends('layouts.app')

@section('breadcrumbs',  Breadcrumbs::render('projects.show', $project))

@section('actions')
<a href="{{ url("users/syncUsers/{$project->id}") }}" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> Sync Users</a>
<a href="{{ url("sprints/syncSprints/{$project->id}") }}" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm ml-3"><i class="fas fa-download fa-sm text-white-50"></i> Sync Milestones</a>
@endsection

@section('container-title', $project->name . ' Milestones')

@section('content')
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link active" href="{{route('projects.projectPane', $project->id)}}" id="project_tab" data-toggle="tabajax" data-target="#project">
                <h6 class="m-0 font-weight-bold">{{ $project->name }}</h6>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('projects.settingsPane', $project->id)}}" id="settings_tab" data-toggle="tabajax" data-target="#settings">

                <h6 class="m-0 font-weight-bold"><i class="fas fa-cog fa-sm text-gray-400"></i>Settings</h6>
            </a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane  urlbox span8" id="awaiting_request">
            <img src="{{asset('img/ajax-loader.gif')}}" style="margin-left: 49%">
        </div>
        <div class="tab-pane active" id="project">
            @include('projects.partials.project', ['project' => $project])
        </div>
        <div class="tab-pane" id="settings">
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <script type="text/javascript">
        $('[data-toggle="tabajax"]').click(function(e) {
            var $this = $(this),
                    loadurl = $this.attr('href'),
                    targ = $this.attr('data-target');

            $.get(loadurl, function(data) {
                $(targ).html(data);
            });

            $this.tab('show');
            return false;
        });

        $(document).ready(function () {
            $(document).on({
                ajaxStart: function(){
                    $('#awaiting_request').show();
                },
                ajaxStop: function(){
                    $('#awaiting_request').hide();
                }
            });
        });

    </script>
@endsection








