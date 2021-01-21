@extends('layouts.app')

@section('head')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
@endsection

@section('container-title', 'Projects')

@section('breadcrumbs',  Breadcrumbs::render('projects'))

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Your Projects (Assembla Spaces)</h6>
                        <a href="{{url("projects/importProjects")}}" style="float: right;">Import Projects</a></div>



                    <div style="margin-left: 40px;">
                        * only checked projects will be considered on the <a href="{{url('sprints/current')}}">Current Sprints</a> page
                    </div>

                        <ul style="list-style: none;">
                            @forelse ($projects as $project)
                                <li>
                                    <div class="starred-form-container" style="float:left;">
                                        <form id="{{$project->id}}" class="starred" name="starred_project_form" action="{{url("projects/starred/{$project->id}")}}" method="POST">
                                            <input class="project-star" name="starred_project" type="checkbox" @if ($project->pivot->starred) checked @endif/>
                                        </form>
                                    </div>

                                    <a href="{{url("projects/{$project->id}")}}">{{ $project->name}}</a>

                                </li>


                            @empty
                                <p>No projects imported yet. What are you waiting for? Import your projects <a href="{{url("projects/importProjects")}}">here!<a/></p>
                            @endforelse
                        </ul>

                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        console.log('Projects page');
        jQuery("[name='starred_project_form']").change('.project-star', function(e) {
            console.log('starred_project updated');
            jQuery.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });
            jQuery.ajax({
                type: 'POST',
                cache: false,
                dataType: 'JSON',
                url: $(this).attr('action'),
                data: $(this).serialize(),
                success: function(data) {
                    console.log(data);
                }
            });
        });
    </script>
@endsection








