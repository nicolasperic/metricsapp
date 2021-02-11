<?php

namespace App\Jobs;

use App\Helper\Helper;
use App\Importer\ProjectImporter;
use App\User;
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
        $this->user->notify(Helper::getAssemblaSyncNotification(
            null,
            route('projects.index'),
            'Spaces were synced correctly'
            ));
    }
}
