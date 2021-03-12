@section('head')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
@endsection
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Space Settings</h6>
            </div>
            <div class="card-body">
                <strong>User Configurations</strong> <small>(following settings will only affect your session)</small>
                <div class="form-group  pl-3">
                    <form id="{{$project->id}}_starred" class="project-setting starred" name="starred_project_form" action="{{ route('projects.storePivotAttribute', $project-> wikiname) }}" method="POST">
                        {{ csrf_field() }}
                        <input type="hidden" name="attribute_name" value="starred">
                        <input name="starred" id="starred" type="checkbox" @if ($project->pivot->starred) checked @endif/>
                        <label for="starred" class="mb-0">Starred space</label>
                        <small class="form-text text-muted ml-4">Adds space to the left menu and current milestones view. Current milestone will be also available on Starred Milestones left menu.</small>
                    </form>
                </div>
                <div class="form-group  pl-3">
                    <form id="{{$project->id}}_syncable" class="project-setting syncable" name="syncable_project_form" action="{{ route('projects.storePivotAttribute', $project->wikiname) }}" method="POST">
                        {{ csrf_field() }}
                        <input type="hidden" name="attribute_name" value="syncable">
                        <input class="project-sync" name="syncable" id="syncable" type="checkbox" @if ($project->pivot->syncable) checked @endif/>
                        <label for="syncable" class="mb-0">Auto Sync space</label>
                        <small class="form-text text-muted ml-4">Space milestones and current milestone tickets related info will be synced dynamically.</small>
                    </form>
                </div>
                <strong>Shared Configurations</strong> <small>(following settings will affect all users with access to the space)</small>
                <div class="form-group  pl-3">
                    <form id="{{$project->id}}_shared" class="project-setting shared" name="shared_project_form" action="{{ route('projects.storeAttribute',$project->wikiname) }}" method="POST">
                        {{ csrf_field() }}
                        <input type="hidden" name="attribute_name" value="shared">
                        <input type="hidden" name="is_checkbox" value="1">
                        <input class="project-shared" name="shared" id="shared" type="checkbox" @if ($project->shared) checked @endif/>
                        <label for="shared" class="mb-0">Shared space</label>
                        <small class="form-text text-muted ml-4">Space is used by many teams. Checking this will enable filtering tracked time by specific users on Hours by Users Report.</small>
                    </form>
                </div>
                <div class="form-group  pl-3">
                    <form id="{{$project->id}}_estimate" class="project-setting estimate" name="estimate_project_form" action="{{ route('projects.storeAttribute',$project->wikiname) }}" method="POST">
                        {{ csrf_field() }}
                        <input type="hidden" name="attribute_name" value="estimate_type">
                        <label for="name" class="mb-0">Estimate by</label>
                        <select name="estimate_type">
                            <option></option>
                            <option value="{{App\Project::ESTIMATE_POINTS}}" @if ($project->estimate_type == App\Project::ESTIMATE_POINTS) selected @endif>Points</option>
                            <option value="{{App\Project::ESTIMATE_TIME}}" @if ($project->estimate_type == App\Project::ESTIMATE_TIME) selected @endif>Time</option>
                            <option value="{{App\Project::ESTIMATE_SIZE}}" @if ($project->estimate_type == App\Project::ESTIMATE_SIZE) selected @endif>Size (small / medium / large)</option>
                        </select>
                        <small class="form-text text-muted ml-4">Update the configuration based on the <a href="https://app.assembla.com/spaces/{{$project->wikiname}}/tickets/settings/ticket-fields#estimate_by" target="_blank">Estimate option<a></a> under ticket settings</small>
                    </form>
                </div>
            </div>
        </div>


        <?php $currentSprint = $project->getCurrentSprint(); ?>
        @if($project->isUserOwner(Auth::user()) && $currentSprint !== null)
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Automate Sprint Iterations</h6>
                </div>

                <div class="card-body">
                    <?php $autoRunningDisabled  = ($sprintIteration->isAutoIterationRunning())?'disabled':'';?>
                    <strong>Iteration Configurations</strong> <small>(following settings will affect all owner users with access to the space)</small>
                    <?php //TODO if iteration status is RUNNING fields should be disabled. Only editable when stopped?>
                    <?php //TODO when start is pressed display a modal to confirm and show the user the title and start, end dates for the sprint?>
                    <div class="form-group pl-3">
                        <form id="{{$project->id}}_sprint_prefix" class="project-setting" name="sprint_prefix_project_form" action="{{ route('iterations.storeAttribute',$project->wikiname) }}" method="POST">
                            {{ csrf_field() }}
                            <input type="hidden" name="attribute_name" value="sprint_prefix">
                            <label for="name" class="mb-0">Milestone Title Prefix</label>
                            <input type="text" name="sprint_prefix" value="{{ $sprintIteration->sprint_prefix }}" {{ $autoRunningDisabled }}>
                            <small class="form-text text-muted ml-4">Title needs to be unique, an auto generated code will be added after the prefix i.e {{ $sprintIteration->getNewMilestoneUniqueTitle(\Carbon\Carbon::today()) }}</small>
                        </form>
                    </div>

                    <div class="form-group pl-3">
                        <form id="{{$project->id}}_sprint_duration" class="project-setting sprint-duration" name="sprint_duration_project_form" action="{{ route('iterations.storeAttribute',$project->wikiname) }}" method="POST">
                            {{ csrf_field() }}
                            <input type="hidden" name="attribute_name" value="sprint_duration">
                            <label for="name" class="mb-0">Milestone Duration</label>
                            <select name="sprint_duration" {{ $autoRunningDisabled }}>
                                <option></option>
                                <option value="{{App\SprintIteration::TWO_WEEKS}}" @if ($sprintIteration->sprint_duration == App\SprintIteration::TWO_WEEKS) selected @endif>2 weeks</option>
                                <option value="{{App\SprintIteration::THREE_WEEKS}}" @if ($sprintIteration->sprint_duration == App\SprintIteration::THREE_WEEKS) selected @endif>3 weeks</option>
                                <option value="{{App\SprintIteration::FOUR_WEEKS}}" @if ($sprintIteration->sprint_duration == App\SprintIteration::FOUR_WEEKS) selected @endif>4 weeks</option>
                            </select>
                            <small class="form-text text-muted ml-4">Set the sprint iteration length in weeks</small>
                        </form>
                    </div>

                    <div class="form-group pl-3">
                        <form id="sprint_start_weekday_form" class="project-setting sprint-start-date" name="sprint_start_weekday_form" action="{{ route('iterations.startDate',$project->wikiname) }}" method="POST">
                            {{ csrf_field() }}
                            <label for="name" class="mb-0">Starting on </label>
                            <select name="sprint_start_weekday" id="sprint_start_weekday" {{ $autoRunningDisabled }}>
                                <option></option>
                                <option value="1" @if ($sprintIteration->sprint_start_weekday == 1) selected @endif>Mondays</option>
                                <option value="2" @if ($sprintIteration->sprint_start_weekday == 2) selected @endif>Tuesdays</option>
                                <option value="3" @if ($sprintIteration->sprint_start_weekday == 3) selected @endif>Wednesdays</option>
                                <option value="4" @if ($sprintIteration->sprint_start_weekday == 4) selected @endif>Thursdays</option>
                                <option value="5" @if ($sprintIteration->sprint_start_weekday == 5) selected @endif>Fridays</option>
                                <option value="6" @if ($sprintIteration->sprint_start_weekday == 6) selected @endif>Saturdays</option>
                                <option value="0" @if ($sprintIteration->sprint_start_weekday === 0) selected @endif>Sundays</option>

                            </select>
                            <small class="form-text text-muted ml-4">Set the sprint starting weekday</small>
                        </form>
                    </div>

                    <div id="iteration-status-container" style="display: none;">
                        <strong>Iteration Status</strong> <small>(start a new iteration or stop current iteration)</small>
                        <div class="form-group  pl-3">
                            @if(!$sprintIteration->isAutoIterationRunning())
                                <form id="{{$project->id}}_iteration_status" class="auto-sprint-iteration-status" name="iteration_status_form" action="{{ route('iterations.start',$project->wikiname) }}" method="POST">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="attribute_name" value="iteration_status">
                                    <input type="hidden" name="is_checkbox" value="1">
                                    <input type="hidden" name="current_sprint_id" value="{{ $currentSprint->sprint_assembla_id }}">

                                    <div id="not_same_day" @if(!isset($sprintIteration->sprint_start_weekday) || $sprintIteration->sprint_start_weekday == $day_of_week) style="display: none" @endif>
                                        <div class="form-group">
                                            <input type="radio" name="start_date" id="last_weekday" value="{{ $start_dates['last_date'] }}">
                                            <label id="last_weekday_label" for="last_weekday" class="mb-0">{{ $start_dates['last'] }}</label>
                                        </div>
                                        <div class="form-group">
                                            <input type="radio" name="start_date" id="next_weekday" value="{{ $start_dates['next_date'] }}">
                                            <label id="next_weekday_label" for="next_weekday" class="mb-0">{{ $start_dates['next'] }}</label>
                                        </div>
                                    </div>

                                    <label for="iteration_status" class="mb-0">Automate Sprint Iteration</label><br>
                                    <label>Current milestone: <strong>{{ $currentSprint->name }}</strong></label><br/>

                                    <button class="btn btn-primary">Start Iteration</button>
                                    <small class="form-text text-muted ml-4">
                                        Starting iteration enables automatic iterations.
                                        New current milestones will be created every <strong>{{$sprintIteration->sprint_duration}} weeks</strong>
                                        with the carry over of the previous current milestone
                                    </small>
                                </form>
                            @else
                                <form id="{{$project->id}}_iteration_status" class="auto-sprint-iteration-status" name="iteration_status_form" action="{{ route('iterations.stop',$project->wikiname) }}" method="POST">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="attribute_name" value="iteration_status">
                                    <input type="hidden" name="is_checkbox" value="1">

                                    <h6 class="m-0 font-weight-bold"><i class="fas fa-user fa-sm text-gray-400"></i>Started By: {{ $sprintIteration->getStartedBy() }}</h6>
                                    <button class="btn btn-danger">Stop Iteration</button>
                                </form>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

</div>

<script type="text/javascript">

    function canDisplayStatusBasedConent() {
        var sprintPrefix = $('input[name="sprint_prefix"]').get(0).value;
        var milestoneDuration = $('select[name="sprint_duration"]').get(0).value;
        var startingOn = $('select[name="sprint_start_weekday"]').get(0).value;

        if (sprintPrefix != '' && milestoneDuration != '' && startingOn != '') {
            $('#iteration-status-container').show();
        } else {
            $('#iteration-status-container').hide();
        }
    }
    $(document).ready(function() {
        canDisplayStatusBasedConent();
    });
    var currentWeekday = {!! $day_of_week !!};

    $("form.project-setting").change(function(e) {
        if ($(this).attr('id') == 'sprint_start_weekday_form') {
            var sprintStartWeekdak = $('#sprint_start_weekday').val();
            if (sprintStartWeekdak != currentWeekday) {
                $('#not_same_day').show();
            } else {
                $('#not_same_day').hide();
            }
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'POST',
            cache: false,
            dataType: 'JSON',
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function(data) {
                canDisplayStatusBasedConent();
                if (data && data.last && data.next) {
                    $('#last_weekday_label').text(data.last);
                    $('#last_weekday').val(data.last_date);
                    $('#next_weekday_label').text(data.next);
                    $('#next_weekday').val(data.next_date);
                }
            }
        });
    });
</script>