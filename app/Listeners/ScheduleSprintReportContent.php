<?php

namespace App\Listeners;

use App\Events\SprintsForReportProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ScheduleSprintReportContent
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SprintsForReportProcessed  $event
     * @return void
     */
    public function handle(SprintsForReportProcessed $event)
    {
        Log::info('ScheduleSprintReportContent handlling');
        $report = $event->getReport();
        Log::info(print_r(unserialize($report->request_data),1));
    }
}
