<?php

namespace App\Jobs;

use App\Report;
use App\Reports\HoursByUSReport;
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
    private $authUser;

    /**
     * Create a new job instance.
     *
     *
     */
    public function __construct($requestData, $authUser)
    {
        //
        $this->requestData = $requestData;
        $this->authUser = $authUser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $reportModel = Report::create([
            'title' => 'Hours by User Story',
            'request_data' => serialize($this->requestData)
        ]);
        $this->authUser->reports()->save($reportModel);

        Log::info('Report '.print_r($this->requestData, 1));
        $report = new HoursByUSReport($this->requestData);
        $reportResults = $report->execute();

        Log::info('Report '.print_r($reportResults, 1));

        $reportModel->status = Report::PROCESSED_STATUS;
        $reportBody = '';
        foreach ($reportResults as $line) {
            $reportBody .= '<p>'.$line.'</p>';
        }
        $reportModel->body = $reportBody;
        $reportModel->save();

    }
}
