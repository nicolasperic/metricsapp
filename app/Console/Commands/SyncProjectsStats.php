<?php

namespace App\Console\Commands;

use App\Jobs\SyncProjectStats;
use App\Project;
use App\User;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

/**
 * This command will dispatch SyncProjectStats job for all projects set as auto sync TRUE
 * For now we are only calculating monthly stats
 * Extra logic required for this kind of model
 * fromDate, toDate, rangeType (week, month, year)//maybe we just keep month
 *
 * Class SyncProjectsStats
 *
 * @package App\Console\Commands
 */
class SyncProjectsStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projectsstats:sync {--year=} {--month=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This process calculates the hours and tasks stats for a given period (month, week, year)';

    /**
     * Create a new command instance.
     *
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
        $year = $this->option('year');
        $month = $this->option('month');

        if ($month < 1 || $month > 12)
            return;

        //workaround to prevent scheduling more than one autosync batch of jobs! (this is for Heroku since the CRON does not awake the worker)
        $unprocessedBatches = DB::table('job_batches')->where('name','=','SyncProjectsStats')->where('pending_jobs','>','0')->count();
        if ($unprocessedBatches !== 0) {
            return;
        }

        //Retrieve all syncable projects to dispatch the required jobs
        $syncableProjects = DB::table('projects') ->join('project_user', function ($join)
        { $join->on('projects.id', '=', 'project_user.project_id') ->where('project_user.syncable',
            '=', true); })->groupBy('projects.id')->distinct()->get();


        //Preparing Space and Current Milestone sync jobs for batch processing
        $jobs = $syncableProjects->map(function (\stdClass $projectData)  use($year, $month) {
            $project = Project::find($projectData->project_id);
            $user = User::find($projectData->user_id);

            return [
                new SyncProjectStats($user, $project, $year, $month)
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
                print 'ProjectsStats batches are done'.PHP_EOL;
            })->name('SyncProjectsStats')->dispatch();;
        return 0;
    }
}
