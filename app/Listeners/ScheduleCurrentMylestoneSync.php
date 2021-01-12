<?php

namespace App\Listeners;

use App\Events\SpaceMilestonesSynced;
use App\Jobs\SyncCurrentMilestone;
use App\Project;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ScheduleCurrentMylestoneSync
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SpaceSynced  $event
     * @return void
     */
    public function handle(SpaceMilestonesSynced $event)
    {
        //retrieve current milestone on project and dispatch sync job
        /** @var Project $project */
        $project = $event->getProject();
        Log::info('Listening to SpaceMilestonesSynced for '.$project->name);
        $currentSprint = $project->getCurrentSprint();
        Log::info('Dispatching SyncCurrentMilestone for '.$project->name);
        SyncCurrentMilestone::dispatch($event->getUser(), $currentSprint);

    }
}
