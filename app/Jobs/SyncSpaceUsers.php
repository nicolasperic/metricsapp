<?php

namespace App\Jobs;

use App\Helper\Helper;
use App\Importer\UserImporter;
use App\Project;
use App\User;
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
        $this->user->notify(Helper::getAssemblaSyncNotification(
            $this->project->id,
            url('projects', $this->project->id),
            $this->project->name.' users were synced correctly'
        ));
    }

}
