<?php

namespace App\Jobs;

use App\Dto\NotificationDto;
use App\Notifications\AssemblaSynced;
use App\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

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
                $notificationDto = new NotificationDto([
                    'entity_id' => null,
                    'url' => url('sprints/current'),
                    'message' => 'Current milestones were synced correctly',
                    'date' => Carbon::now()->format('F d, Y g:i a'),
                    'bg_class' => 'bg-success',
                    'icon_class' =>'fa-sync'
                ]);
                $user->notify(new AssemblaSynced($notificationDto));

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
