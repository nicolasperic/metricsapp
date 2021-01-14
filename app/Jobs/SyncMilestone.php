<?php

namespace App\Jobs;

use App\Importer\TicketImporter;
use App\Sprint;
use App\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMilestone implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var User
     */
    private $user;
    /**
     * @var Sprint
     */
    private $sprint;

    /**
     * @param User   $user
     * @param Sprint $sprint
     */
    public function __construct(User $user, Sprint $sprint)
    {
        $this->user = $user;
        $this->sprint = $sprint;
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

        try {

            Log::info('SyncMilestone executing for '.$this->sprint->refresh()->title);
            $ticketImporter = new TicketImporter($this->user);
            $ticketImporter->importMilestoneTickets($this->sprint);
            Log::info('SyncMilestone ended for '.$this->sprint->title);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            //TODO see how to block a batch if in batch?
        }
    }
}
