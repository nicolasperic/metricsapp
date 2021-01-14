<?php

namespace App\Console\Commands;

use App\Jobs\SyncSpaceCurrentMilestone;
use App\Jobs\SyncSpaceMilestones;
use App\Project;
use App\User;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
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

        //Retrieve all syncable projects to dispatch the required jobs
        $syncableProjects = DB::table('projects') ->join('project_user', function ($join)
        { $join->on('projects.id', '=', 'project_user.project_id') ->where('project_user.syncable',
            '=', true); })->groupBy('projects.id')->distinct()->get();

        //Preparing Space and Current Milestone sync jobs for batch processing
        $jobs = $syncableProjects->map(function (\stdClass $projectData)  {
            $project = Project::find($projectData->project_id);
            $user = User::find($projectData->user_id);

            return [
                new SyncSpaceMilestones($user, $project),
                new SyncSpaceCurrentMilestone($user, $project)
            ];
        })
            ->filter()
            ->collapse()
            ->toArray();

        Bus::batch($jobs)
            ->then(function (Batch $batch) {
                // All jobs completed successfully...
            })->catch(function (Batch $batch, Throwable $e) {
                // First batch job failure detected...
            })->finally(function (Batch $batch) {
                print 'AutoSync batches are done'.PHP_EOL;
            })->dispatch();;
        return 0;
    }
}
