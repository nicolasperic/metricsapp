@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Set your weekly report</div>
                    <span style="margin-left: 20px">* report will be generated each Monday with the last week time entries</span>
                    <form method="POST" action="{{url("reports/weeklyStore")}}" style="padding: 20px;">
                        @csrf


                        <div class="field">
                            <label class="label" for="title">Projects</label>
                            <div class="control">
                                @if (count($projects))
                                    <select name="projects[]"  id="projects" multiple>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->project_assembla_id }}" @if(is_array($selectedProjects) && in_array($project->project_assembla_id, $selectedProjects)) selected @endif data-wikiname="{{ $project->wikiname }}">{{ $project->name }}</option>
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
                                            <option value="{{ $userAssemblaId }}" @if(is_array($selectedUsers) && in_array($userAssemblaId, $selectedUsers)) selected @endif>{{ $userName }}</option>
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


                            <div style="margin-top:20px;">
                                <button class="btn btn-sm btn-primary shadow-sm">Save Report Settings</button>
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

    #users, #projects {
        height: 200px;
    }


</style>







