<?php

namespace App\Jobs;

use App\Exceptions\SprintIterationException;
use App\Helper\Helper;
use App\Sprint;
use App\SprintIteration as SprintIterationModel;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SprintIteration implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var User
     */
    private $user;
    /**
     * @var SprintIterationModel
     */
    private $sprintIteration;
    /**
     * @var
     */
    private $startDate;

    /**
     * Create a new job instance.
     *
     * @param User   $user
     * @param Sprint $sprint
     * @param string $startDate format Y/m/d
     */
    public function __construct(User $user, SprintIterationModel $sprintIteration, $startDate = false)
    {
        $this->user = $user;
        $this->sprintIteration = $sprintIteration;
        $this->startDate = $startDate;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $newSprint = $this->sprintIteration->iterate($this->startDate);
            $project = $this->sprintIteration->project;

            
            $this->user->notify(Helper::getAssemblaSyncNotification(
                $newSprint->id,
                route('sprints.show', [$project->wikiname, $newSprint->sprint_assembla_id]),'Sprint Iteration succeeded! '.
                $newSprint->name.' was created correctly'
            ));

        } catch (SprintIterationException $e) {
            $this->user->notify(Helper::getAssemblaSyncNotification(
                null,
                route('home'),
                $e->getMessage(),
                'bg-warning'
            ));
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
