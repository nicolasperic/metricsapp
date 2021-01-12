<?php

namespace App\Jobs;

use App\Importer\TicketImporter;
use App\Sprint;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * This JOB is reponsible of importing/updating a milestone calling the Assembla API and updating
 * the local database to keep data synced
 *
 * We will only use this JOB for current milestones although it could work with any type of milestone
 *
 * The user can select which spaces would like to keep synced, for those spaces the current milestone
 * will be the only milestone synced dynamically
 *
 * Class SyncCurrentMilestone
 *
 * @package App\Jobs
 */
class SyncCurrentMilestone implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Sprint
     */
    private $sprint;
    /**
     * @var User
     */
    private $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, Sprint $sprint)
    {
        //
        $this->user = $user;
        $this->sprint = $sprint;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //this jobs needs to listen SYncSpaceMilestones
        try {
            Log::info('SyncCurrentMilestone executing for '.$this->sprint->title);
            $ticketImporter = new TicketImporter($this->user);
            $ticketImporter->importMilestoneTickets($this->sprint);
            Log::info('SyncCurrentMilestone ended for '.$this->sprint->title);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        }
        //CRON
        //1> Obtener los spaces cuyo "aut_sync" is true
        //2> Sincronizar los milestones

        //SyncMilestone
        //3> Obtengo el current actual y lo sincronizo
        //4> "Flag" en sprints para validar último sync
    }
}
