<?php

namespace App\Jobs;

use App\Importer\SprintImporter;
use App\Project;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * This JOB is responsible of keeping milestones updated for a given Space calling the Assembla API and updating
 * the local database to keep data synced
 *
 * This JOB will be used for the "auto sync" feature on Spaces
 *
 * Class SyncSpacesMilestones
 *
 * @package App\Jobs
 */
class SyncSpaceMilestones implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Project
     */
    private $project;

    /**
     * Create a new job instance.
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $sprintImporter = new SprintImporter();
            $sprintImporter->importProjectMilestonesAsSprints($this->project);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

    }
}
