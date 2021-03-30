<?php

namespace App\Jobs;

use App\Importer\UserImporter;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncUser implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var User
     */
    private $user;

    private $userImporter;
    /**
     * @var
     */
    private $userAssemblaId;
    /**
     * @var
     */
    private $project;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param string $userAssemblaId
     */
    public function __construct(User $user, $userAssemblaId, $project)
    {
        $this->userImporter = new UserImporter($user);
        $this->user = $user;
        $this->userAssemblaId = $userAssemblaId;
        $this->project = $project;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->userImporter->importUser($this->userAssemblaId);
        if ($user !== false) {
            $this->project->assemblaUsers()->save($user);
        }
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->userAssemblaId;
    }
}
