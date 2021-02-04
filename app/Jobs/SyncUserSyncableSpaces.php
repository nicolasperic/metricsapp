<?php

namespace App\Jobs;

use App\Helper\Helper;
use App\Project;
use App\User;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class SyncUserSyncableSpaces implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var User
     */
    private $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $jobs = $this->user->syncableProjects->map(function (Project $project)  {
            return [
                new SyncSpaceMilestones($this->user, $project, false),
                new SyncSpaceCurrentMilestone($this->user, $project)
            ];
        })
            ->filter()
            ->collapse()
            ->toArray();
        $user = $this->user;

        Bus::batch($jobs)
            ->then(function (Batch $batch) {
            // All jobs completed successfully...
        })->catch(function (Batch $batch, Throwable $e) {
            // First batch job failure detected...
        })->finally(function (Batch $batch) use($user) {
            print 'Print all batches are done'.PHP_EOL;
            $user->notify(Helper::getAssemblaSyncNotification(
                null,
                route('sprints.current'),
                'Current milestones were synced correctly'
            ));

        })->dispatch();
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->user->id;
    }
}
