<?php

namespace App\Jobs;

use App\Helper\Helper;
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

class SyncMilestone implements ShouldQueue//, ShouldBeUnique
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
     * @var bool
     */
    private $notify;

    /**
     * @param User   $user
     * @param Sprint $sprint
     */
    public function __construct(User $user, Sprint $sprint, $notify = true)
    {
        $this->user = $user;
        $this->sprint = $sprint;
        $this->notify = $notify;
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
            $noContent = $ticketImporter->importMilestoneTickets($this->sprint);
            Log::info('SyncMilestone ended for '.$this->sprint->title);

            $project = $this->sprint->getProject();
            if ($this->notify) {
                $message = $this->sprint->name.' was synced correctly';
                $bgClass = 'bg-success';
                if ($noContent) {
                    $message = 'No tickets were retrieved for '.$this->sprint->name;
                    $bgClass = 'bg-warning';
                }

                $this->user->notify(Helper::getAssemblaSyncNotification(
                    $this->sprint->id,
                    route('sprints.show', [$project->wikiname, $this->sprint->sprint_assembla_id]),
                    $message,
                    $bgClass
                ));
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            //TODO see how to block a batch if in batch?
        }
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    /*public function uniqueId()
    {
        return $this->sprint->sprint_assembla_id;
    }*/

}
