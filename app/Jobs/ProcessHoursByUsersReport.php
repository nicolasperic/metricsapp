<?php

namespace App\Jobs;

use App\Notifications\WeeklyReportNotification;
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
use Illuminate\Support\Facades\Notification;

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
            $this->reportModel->execute();
            if ($this->sendEmail) {
                $user = $this->reportModel->user;
                $subject = 'Weekly Report ('.$this->requestData['from_date'].' - '.$this->requestData['to_date'].')';
                Notification::route('mail', $user->email)->notify(new WeeklyReportNotification($this->reportModel, $subject));
            }
        } catch (\Exception $e) {
            $this->reportModel->failed();
            Log::info($e->getMessage());
        }

    }
}
