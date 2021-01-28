<?php

namespace App\Jobs;

use App\Dto\NotificationDto;
use App\Importer\ProjectImporter;
use App\Notifications\AssemblaSynced;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncSpaces implements ShouldQueue
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
        //
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $projectImporter = new ProjectImporter($this->user);
        $projectImporter->importAllAssemblaSpacesAsProjects();
        $this->user->notify($this->getAssemblaSyncNotification());
    }

    private function getAssemblaSyncNotification()
    {
        $notificationDto = new NotificationDto([
            'entity_id' => null,
            'url' => route('projects.index'),
            'message' => 'Projects were synced correctly',
            'date' => Carbon::now()->format('F d, Y g:i a'),
            'bg_class' => 'bg-success',
            'icon_class' =>'fa-sync'
        ]);
        return new AssemblaSynced($notificationDto);
    }
}
