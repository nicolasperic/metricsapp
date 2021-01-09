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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
     * @var bool
     */
    private $sendEmail;

    /**
     * Create a new job instance.
     * @param $requestData
     * @param $reportModel
     */
    public function __construct($requestData, $reportModel, $sendEmail = false)
    {
        $this->requestData = $requestData;
        $this->reportModel = $reportModel;
        $this->sendEmail = $sendEmail;
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
            $this->reportModel->status = Report::PROCESSED_STATUS;

            if ($this->sendEmail) {
                $user = $this->reportModel->user;
                Mail::raw($reportBody, function ($mail) use ($user) {
                    $mail->to($user->email)
                        ->subject('Weekly Report ('.$this->requestData['from_date'].' - '.$this->requestData['to_date']);
                });
            }
        } catch (\Exception $e) {
            $this->reportModel->status = Report::FAILED_STATUS;
            Log::info($e->getMessage());
        } finally {
            $this->reportModel->finished_at = Carbon::now();
            $this->reportModel->save();
        }

    }
}
