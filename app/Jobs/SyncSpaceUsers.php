<?php

namespace App\Jobs;

use App\Dto\NotificationDto;
use App\Importer\UserImporter;
use App\Notifications\AssemblaSynced;
use App\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncSpaceUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Project
     */
    private $project;
    /**
     * @var User
     */
    private $user;

    /**
     * Create a new job instance.
     * @param Project $project
     */
    public function __construct(User $user, Project $project)
    {
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
        $userImporter = new UserImporter($this->user);
        $userImporter->importSpaceUsers($this->project);
        $this->user->notify($this->getAssemblaSyncNotification());
    }

    private function getAssemblaSyncNotification()
    {
        $notificationDto = new NotificationDto([
            'entity_id' => $this->project->id,
            'url' => url('projects', $this->project->id),
            'message' => $this->project->name.' users were synced correctly',
            'date' => Carbon::now()->format('F d, Y g:i a'),
            'bg_class' => 'bg-success',
            'icon_class' =>'fa-sync'
        ]);
        return new AssemblaSynced($notificationDto);
    }
}
