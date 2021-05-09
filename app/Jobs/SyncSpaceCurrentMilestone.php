<?php

namespace App\Jobs;

use App\Importer\TicketImporter;
use App\Project;
use App\Sprint;
use App\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * This JOB is reponsible of importing/updating the space current milestone calling the Assembla API and updating
 * the local database to keep data synced
 *
 * We will only use this JOB for current milestones although it could work with any type of milestone
 * because of how jobs are dispatched on batches when syncing all current milestones on syncable spaces
 *
 * The user can select which spaces would like to keep synced, for those spaces the current milestone
 * will be the only milestone synced dynamically
 *
 * Class SyncSpaceCurrentMilestone
 *
 * @package App\Jobs
 */
class SyncSpaceCurrentMilestone implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Sprint
     */
    private $sprint;
    /**
     * @var Project
     */
    private $project;
    /**
     * @var User
     */
    private $user;

    /**
     * @param User    $user
     * @param Project $project
     */
    public function __construct(User $user, Project $project)
    {
        //receiving the project and not the sprint because of how we dispatch jobs
        //we dispatch SyncSpaceMilestones and at the same time SyncCurrentMilestone.
        // Since we can' tell the current milestone at that time we send the project
        $this->user = $user;
        $this->project = $project;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...
            return;
        }

        //this jobs needs to listen SYncSpaceMilestones
        try {
            $this->sprint = $this->project->refresh()->getCurrentSprint();
            if ($this->sprint) {
                Log::info('SyncSpaceCurrentMilestone executing for '.$this->sprint->title);
                $ticketImporter = new TicketImporter($this->user);
                $ticketImporter->importMilestoneTickets($this->sprint);
                Log::info('SyncSpaceCurrentMilestone ended for '.$this->sprint->title);
            } else {
                Log::info('SyncSpaceCurrentMilestone project has no current milestone '.$this->project->wikiname);
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            //TODO see how to block a batch if in batch?
        }
        //CRON
        //1> Obtener los spaces cuyo "aut_sync" is true
        //2> Sincronizar los milestones

        //SyncMilestone
        //3> Obtengo el current actual y lo sincronizo
        //4> "Flag" en sprints para validar último sync
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
