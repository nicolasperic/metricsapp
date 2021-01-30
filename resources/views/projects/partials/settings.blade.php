@section('head')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
@endsection
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card shadow mb-8">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Project Settings</h6>
            </div>
            <div class="card-body">


                    <strong>User Configurations</strong> <small>(following settings will only affect your session)</small>
                    <div class="form-group">
                        <form id="{{$project->id}}" class="starred" name="starred_project_form" action="{{url("projects/starred/{$project->id}")}}" method="POST">
                            <input class="project-setting" name="starred_project" type="checkbox" @if ($project->pivot->starred) checked @endif/>
                            <label for="name" class="mb-0">Starred project</label>
                            <small id="starredHelp" class="form-text text-muted ml-4">Adds project to the left menu and current sprints view.</small>
                        </form>
                    </div>
                    <div class="form-group">
                        <form id="{{$project->id}}" class="syncable" name="syncable_project_form" action="{{url("projects/syncable/{$project->id}")}}" method="POST">
                            <input class="project-sync" name="syncable_project" type="checkbox" @if ($project->pivot->syncable) checked @endif/>
                            <label for="name" class="mb-0">Auto Sync project</label>
                            <small id="syncableHelp" class="form-text text-muted ml-4">Project milestones and current milestone will be synced dynamically.</small>
                        </form>
                    </div>
                    <strong>Shared Configurations</strong> <small>(following settings will affect all users with access to the project)</small>
                    <div class="form-group">
                        <form id="{{$project->id}}" class="shared" name="shared_project_form" action="{{url("projects/shared/{$project->id}")}}" method="POST">
                            <input class="project-shared" name="shared_project" type="checkbox" @if ($project->shared) checked @endif/>
                            <label for="name" class="mb-0">Shared project</label>
                            <small id="starredHelp" class="form-text text-muted ml-4">Project is used by many teams. Checking this will enable filtering tracked time by specific users on Hours by Users Report.</small>
                        </form>
                    </div>
                    <div class="form-group">
                        <form id="{{$project->id}}" class="estimate" name="estimate_project_form" action="{{url("projects/estimate/{$project->id}")}}" method="POST">
                            <label for="name" class="mb-0">Estimate by</label>
                            <select name="estimate_type">
                                <option></option>
                                <option value="{{App\Project::ESTIMATE_POINTS}}" @if ($project->estimate_type == App\Project::ESTIMATE_POINTS) selected @endif>Points</option>
                                <option value="{{App\Project::ESTIMATE_TIME}}" @if ($project->estimate_type == App\Project::ESTIMATE_TIME) selected @endif>Time</option>
                                <option value="{{App\Project::ESTIMATE_SIZE}}" @if ($project->estimate_type == App\Project::ESTIMATE_SIZE) selected @endif>Size (small / medium / large)</option>
                            </select>
                            <small id="starredHelp" class="form-text text-muted ml-4">Update the configuration based on the <a href="https://app.assembla.com/spaces/{{$project->wikiname}}/tickets/settings/ticket-fields#estimate_by" target="_blank">Estimate option<a></a> under ticket settings</small>
                        </form>
                    </div>
            </div>
        </div>
    </div>

</div>

<script type="text/javascript">
    console.log('settings');
    jQuery("form").change('.project-setting', function(e) {
        console.log('something changed');
        jQuery.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        jQuery.ajax({
            type: 'POST',
            cache: false,
            dataType: 'JSON',
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function(data) {
            }
        });
    });
</script>