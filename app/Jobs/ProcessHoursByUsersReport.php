<?php

namespace App\Jobs;

use App\Report;
use App\Reports\HoursByUserReport;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessHoursByUsersReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var
     */
    private $requestData;
    /**
     * @var
     */
    private $reportModel;

    /**
     * Create a new job instance.
     * @param $requestData
     * @param $reportModel
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
            $this->reportModel->status = Report::RUNNING_STATUS;
            $this->reportModel->save();

            $report = new HoursByUserReport($this->requestData);
            $reportResults = $report->execute();

            $reportBody = '';
            foreach ($reportResults as $line) {
                $reportBody .= $line;
            }
            $this->reportModel->body = $reportBody;
            $this->reportModel->status = Report::PROCESSED_STATUS;//TODO si no importo Report puedo simular un exception para ver el status failed
        } catch (\Exception $e) {
            $this->reportModel->status = Report::FAILED_STATUS;
        } finally {
            $this->reportModel->finished_at = Carbon::now();
            $this->reportModel->save();
        }

    }
}
