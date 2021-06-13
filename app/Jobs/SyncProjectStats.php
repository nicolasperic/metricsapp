<?php

namespace App\Jobs;

use App\Importer\ProjectImporter;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncProjectStats implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var
     */
    private $user;
    /**
     * @var
     */
    private $project;
    /**
     * @var
     */
    private $year;
    /**
     * @var
     */
    private $month;

    /**
     * Create a new job instance.
     *
     * @param $user
     * @param $project
     * @param $year
     * @param $month
     */
    public function __construct($user, $project, $year, $month)
    {
        $this->user = $user;
        $this->project = $project;
        $this->year = $year;
        $this->month = $month;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->batch() && $this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...
            return;
        }

        $projectImporter = new ProjectImporter($this->user);
        $projectImporter->calculateAndStoreProjectStatsFor($this->project, $this->year, $this->month);
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->project->project_assembla_id.'_'.$this->year.'_'.$this->month;
    }
}
