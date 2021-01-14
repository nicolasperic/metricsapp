<?php

namespace App\Jobs;

use App\Events\SprintsForReportProcessed;
use App\Helper\Helper;
use App\Project;
use App\Report;
use App\Sprint;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
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
    public function __construct($requestData, Report $reportModel, $sendEmail = false)
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
            $this->reportModel->status = Report::RUNNING_STATUS;
            $this->reportModel->save();
            $reportModel = $this->reportModel;

            $jobs = $this->reportModel->user->sprints()->whereIn('sprint_assembla_id', $this->requestData['sprints'])->get()->map(function (Sprint $sprint)  {
                Log::info('Dispatch for '.$sprint->name);
                return [
                    new SyncMilestone($this->reportModel->user, $sprint)
                    //new SyncSpaceCurrentMilestone($this->reportModel->user, $project)
                ];
            })
                ->filter()
                ->collapse()
                ->toArray();

            Bus::batch($jobs)
                ->then(function (Batch $batch) {
                    // All jobs completed successfully...
                })->catch(function (Batch $batch, $e) use ($reportModel) {
                    // First batch job failure detected...
                    $reportModel->status = Report::PROCESSED_STATUS;
                    $reportModel->finished_at = Carbon::now();
                    $reportModel->save();
                })->finally(function (Batch $batch) use ($reportModel) {
                    print 'Print all batches are done'.PHP_EOL;
                    //SprintsForReportProcessed::dispatch($reportModel);



                    $reportBody = '';
                    $totalEstimate = 0;$totalRemainingEstimate = 0;$totalCompletedEstimate = 0; $totalWorkedHours = 0; $totalRemainingHours = 0; $totalStories = 0; $totalCompletedStories = 0;
                    $totalSubtasks = 0; $totalCompletedSubtasks = 0;
                    $sprints = $reportModel->user->sprints()->whereIn('sprint_assembla_id', unserialize($reportModel->request_data)['sprints'])->get();
                    Log::info(count($sprints). " cuenta de sprints");
                    foreach ($sprints as $sprint) {
                        $sprintTotalWorkedHours = $sprint->getTotalWorkedHours();
                        $sprintTotalRemainingHours = $sprint->getTotalWorkingHours();
                        $sprintTotalStories = $sprint->getTotalStories();
                        $sprintTotalCompletedStories = $sprint->getCompletedStories();
                        $sprintRemainingEstimate = $sprint->getTotalRemainingEstimate();
                        $sprintCompletedEstimate = $sprint->getTotalCompletedEstimate();
                        $sprintTotalEstimate = $sprint->getTotalEstimate();

                        $sprintTotalSubtasks = $sprint->getTotalSubtasks();
                        $sprintTotalCompletedSubtasks = $sprint->getCompletedSubtasks();


                        $remainingEstimatePercentage = ($sprint->getTotalCompletedEstimatePercentage() != 0)?100 - $sprint->getTotalCompletedEstimatePercentage():0;

                        $totalRemainingEstimate += $sprintRemainingEstimate;
                        $totalCompletedEstimate += $sprintCompletedEstimate;
                        $totalEstimate          += $sprintTotalEstimate;
                        $totalWorkedHours       += $sprintTotalWorkedHours;
                        $totalRemainingHours    += $sprintTotalRemainingHours;
                        $totalStories           += $sprintTotalStories;
                        $totalCompletedStories  += $sprintTotalCompletedStories;
                        $totalSubtasks          += $sprintTotalSubtasks;
                        $totalCompletedSubtasks += $sprintTotalCompletedSubtasks;

                        $reportBody .= "========================================".PHP_EOL;
                        $reportBody .= $sprint->getProjectName().' > '.$sprint->name.PHP_EOL;
                        $reportBody .= "========================================".PHP_EOL;
                        $reportBody .= 'Worked hours '.$sprintTotalWorkedHours.PHP_EOL;
                        $reportBody .= 'Remaining hours '.$sprintTotalRemainingHours.PHP_EOL;
                        $reportBody .= 'Stories '.$sprintTotalStories."[ $sprintTotalCompletedStories completed, ".$sprint->getPercentCompletedStories()."%]".PHP_EOL;
                        $reportBody .= 'Subtasks '.$sprintTotalSubtasks."[ $sprintTotalCompletedSubtasks completed, ".$sprint->getPercentCompletedSubtasks()."%]".PHP_EOL;
                        $reportBody .= 'Remaining Estimates '.$sprintRemainingEstimate. '('.$remainingEstimatePercentage.'%)'.PHP_EOL;
                        $reportBody .= 'Completed Estimates '.$sprintCompletedEstimate.' ('.$sprint->getTotalCompletedEstimatePercentage().'%)'.PHP_EOL;
                        $reportBody .= 'Total Estimates '.$sprintTotalEstimate.PHP_EOL;
                        $reportBody .= 'Assembla URL '."https://app.assembla.com/spaces/".$sprint->getProject()->wikiname ."/milestones/".$sprint->sprint_assembla_id.PHP_EOL;
                    }

                    if (count($sprints) > 1) {
                        $totalCompletedEstimatePercentage = ($totalEstimate != 0)? number_format($totalCompletedEstimate/$totalEstimate*100,2):0;
                        $reportBody .= "========================================".PHP_EOL;
                        $reportBody .= "Total".PHP_EOL;
                        $reportBody .= "========================================".PHP_EOL;
                        $reportBody .= 'Total Worked hours '.$totalWorkedHours.PHP_EOL;
                        $reportBody .= 'Total Remaining hours '.$totalRemainingHours.PHP_EOL;
                        $reportBody .= 'Total Stories '.$totalStories."[ $totalCompletedStories completed, ".Helper::getPercentageValue($totalCompletedStories, $totalStories)."%]".PHP_EOL;
                        $reportBody .= 'Total Subtasks '.$totalSubtasks."[ $totalCompletedSubtasks completed, ".Helper::getPercentageValue($totalCompletedSubtasks, $totalSubtasks)."%]".PHP_EOL;
                        $remainingEstimate = 100 - $totalCompletedEstimatePercentage;
                        $reportBody .= 'Total Remaining Estimates '.$totalRemainingEstimate. '('. $remainingEstimate.'%)'.PHP_EOL;
                        $reportBody .= 'Total Completed Estimates '.$totalCompletedEstimate.' ('.$totalCompletedEstimatePercentage.'%)'.PHP_EOL;
                        $reportBody .= 'Total Estimates '.$totalEstimate.PHP_EOL;
                    }


                    $reportModel->body = $reportBody;
                    $reportModel->status = Report::PROCESSED_STATUS;
                    $reportModel->finished_at = Carbon::now();
                    $reportModel->save();


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
