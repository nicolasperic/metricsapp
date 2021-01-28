<?php

namespace App\Jobs;

use App\Report;
use App\Reports\SprintsReport;
use App\Sprint;
use App\User;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class ProcessSprintsReport implements ShouldQueue//, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var
     */
    private $requestData;
    /**
     * @var Report
     */
    private $reportModel;
    /**
     * @var bool
     */
    private $sendEmail;
    /**
     * @var User
     */
    private $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($requestData, SprintsReport $reportModel, $sendEmail = false)
    {
        //
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
            $this->reportModel->start();

            $reportModel = $this->reportModel;

            $jobs = $this->reportModel->user->sprints()->whereIn('sprint_assembla_id', $this->requestData['sprints'])->get()->map(function (Sprint $sprint)  {
                Log::info('Dispatch for '.$sprint->name);
                return [new SyncMilestone($this->reportModel->user, $sprint)];
            })
                ->filter()
                ->collapse()
                ->toArray();

            Bus::batch($jobs)
                ->then(function (Batch $batch) {
                    // All jobs completed successfully...
                })->catch(function (Batch $batch, $e) use ($reportModel) {
                    // First batch job failure detected...
                })->finally(function (Batch $batch) use ($reportModel) {
                    print 'Print all batches are done'.PHP_EOL;
                    $reportModel->execute();
                })->dispatch();
        } catch (\Exception $e) {
            $this->reportModel->status = Report::FAILED_STATUS;
            $reportModel->save();
            Log::info($e->getMessage());
            Log::info($e->getTraceAsString());
        }


    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->reportModel->user->id;
    }
}
