<?php

namespace App\Console\Commands;

use App\Jobs\SyncSpaceMilestones;
use App\Project;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assembla:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        //Retrieve all syncable projects
        //Dispatch SyncSpaceMilestones job

        $syncableProjects = DB::table('projects') ->join('project_user', function ($join)
        { $join->on('projects.id', '=', 'project_user.project_id') ->where('project_user.syncable',
            '=', true); })->groupBy('projects.id')->distinct()->get();

        foreach ($syncableProjects as $projectData){
            $user = User::find($projectData->user_id);
            $project = Project::find($projectData->project_id);

            Log::info("Dispatch $project->name milestones sync on AutoSync");
            SyncSpaceMilestones::dispatch($user, $project);

        }
        return 0;
    }
}
