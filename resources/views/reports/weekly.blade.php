@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Set your weekly report</h6>
                    </div>
                    <small class="ml-4">Report will be generated each Monday with the last week time entries</small>
                    <form method="POST" action="{{url("reports/weeklyStore")}}" style="padding: 20px;">
                        @csrf


                        <div class="field">
                            <label class="label" for="title">Spaces</label>
                            <div class="control">
                                @if (count($projects))
                                    <select name="projects[]"  id="projects" multiple>
                                        @foreach($projects as $project)
                                            <option shared="{{ $project->shared }}" value="{{ $project->project_assembla_id }}" @if(is_array($selectedProjects) && in_array($project->project_assembla_id, $selectedProjects)) selected @endif data-wikiname="{{ $project->wikiname }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('projects')
                                    <p class="help is-danger">{{ $errors->first('projects') }}</p>
                                    @enderror
                                @else
                                    No spaces yet, <a href="{{ route('projects.sync') }}">Import spaces</a>
                                @endif
                            </div>
                        </div>

                        <div class="field mt-4" id="users-field" style="display: none;">
                            <label class="label d-block" for="title">Users</label>
                            <small>You've selected a shared space, filter the tracked time by selecting your team. Only shared spaces will be filtered.</small>
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
                                    No users yet, users can be imported by space from each space page</a>
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
        let shared = 0;
        $(document).ready( function () {
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
        $('#projects').change(function (e) {
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

    #users, #projects {
        height: 200px;
    }


</style>







