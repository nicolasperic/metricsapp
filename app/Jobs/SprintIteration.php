<?php

namespace App\Jobs;

use App\Helper\Helper;
use App\Sprint;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SprintIteration implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var User
     */
    private $user;
    /**
     * @var Sprint
     */
    private $sprint;
    /**
     * @var
     */
    private $startDate;
    /**
     * @var
     */
    private $endDate;

    /**
     * Create a new job instance.
     *
     * @param User   $user
     * @param Sprint $sprint
     * @param string $startDate format Y/m/d
     */
    public function __construct(User $user, Sprint $sprint, $startDate, $endDate)
    {
        $this->user = $user;
        $this->sprint = $sprint;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $iterationProcess = new \App\Importer\SprintIteration($this->user);
            $newSprint = $iterationProcess->closeCurrentSprintAndCreateNewOneWithCarryOver($this->sprint, $this->startDate, $this->endDate);
            $project = $newSprint->getProject();
            $project->sprintIteration->iterate();
            
            
            $this->user->notify(Helper::getAssemblaSyncNotification(
                $newSprint->id,
                route('sprints.show', [$project->wikiname, $newSprint->sprint_assembla_id]),
                $newSprint->name.' was created correctly'
            ));
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            Log::info($e->getTraceAsString());
        }

    }
    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->sprint->sprint_assembla_id;
    }
}
