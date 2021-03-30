<?php

namespace App\Jobs;

use App\Report;
use App\Reports\HoursByUSReport;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessUserStoryReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var array with required data for user story report
     */
    private $requestData;
    /**
     * @var
     */
    private $reportModel;


    /**
     * Create a new job instance.
     *
     *
     */
    public function __construct($requestData, HoursByUSReport $reportModel)
    {
        //
        $this->requestData = $requestData;
        $this->reportModel = $reportModel;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->reportModel->execute();
        } catch (\Exception $e) {
            $this->reportModel->status = Report::FAILED_STATUS;
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
        } finally {
            $this->reportModel->finished_at = Carbon::now();
            $this->reportModel->save();
        }


    }
}
