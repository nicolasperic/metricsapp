<?php

namespace App\Jobs;

use App\Helper\Helper;
use App\Importer\SprintImporter;
use App\Project;
use App\User;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
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
class SyncSpaceMilestones implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Project
     */
    private $project;
    /**
     * @var User
     */
    private $user;
    /**
     * @var bool when jobs are dispatched on batches we don't wont to notify the user
     */
    private $notify;

    /**
     * Create a new job instance.
     * @param Project $project
     */
    public function __construct(User $user, Project $project, $notify = true)
    {
        $this->user = $user;
        $this->project = $project;
        $this->notify = $notify;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->batch() && $this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...
            return;
        }

        try {
            $sprintImporter = new SprintImporter($this->user);
            $sprintImporter->importProjectMilestonesAsSprints($this->project);
            if ($this->notify) {
                $this->user->notify(Helper::getAssemblaSyncNotification(
                    $this->project->id,
                    route('projects.show', $this->project->wikiname),
                    $this->project->name.' milestones were synced correctly'
                ));
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }

    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->project->project_assembla_id;
    }
}
