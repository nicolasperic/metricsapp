<?php

namespace App\Jobs;

use App\Dto\NotificationDto;
use App\Importer\TicketImporter;
use App\Notifications\AssemblaSynced;
use App\Sprint;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
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

            $this->user->notify($this->getAssemblaSyncNotification());
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

    private function getAssemblaSyncNotification()
    {
        $notificationDto = new NotificationDto([
                'entity_id' => $this->sprint->id,
                'url' => url('sprints', $this->sprint->id),
                'message' => $this->sprint->name.' was synced correctly',
                'date' => Carbon::now()->format('F d, Y g:i a'),
                'bg_class' => 'bg-success',
                'icon_class' =>'fa-sync'
            ]);
        return new AssemblaSynced($notificationDto);
    }

}
