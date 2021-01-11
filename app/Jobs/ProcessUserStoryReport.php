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
    public function __construct($requestData, $reportModel)
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
            //TODO report->execute() should flag the report model as started
            //tal vez cada proceso de reporte debería extender de Elqouent Report?
            $this->reportModel->status = Report::RUNNING_STATUS;
            $this->reportModel->save();

            $report = new HoursByUSReport($this->requestData, $this->reportModel->user);
            $reportResults = $report->execute();

            $reportBody = '';//TODO this function is 95% like the handle function from Users report
            foreach ($reportResults as $line) {
                $reportBody .= $line;
            }
            $this->reportModel->body = $reportBody;
            $this->reportModel->status = Report::PROCESSED_STATUS;
        } catch (\Exception $e) {
            $this->reportModel->status = Report::FAILED_STATUS;
        } finally {
            $this->reportModel->finished_at = Carbon::now();
            $this->reportModel->save();
        }


    }
}
