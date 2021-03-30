<?php

namespace App\Reports;

use App\Dto\TicketAssociationDto;
use App\Dto\TicketDto;
use App\Helper\Helper;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use App\Report;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

/**
 * Class HoursByUSReport
 *
 * @package App\Reports
 */
class SprintsReport extends Report implements ReportInterface
{
    use HasFactory;

    const VIEW = 'reports.sprints';

    protected $table = 'reports';

    const REPORT_TYPE = 'sprints_report';

    /**
     * @var array information required for the report
     * KEYS: wikiname, space_id, from_date and to_date
     */
    private $requestData;



    public static function forUser(User $user, $requestData)
    {
        return self::create([
            'user_id' => $user->id,
            'request_data' => $requestData,
            'title' => 'Milestones'
        ]);
    }




    function execute()
    {
        $this->requestData = $this->request_data;



        $reportBody = '';
        $reportArrayBody = ['sprints' => []];
        $totalEstimate = 0;$totalRemainingEstimate = 0;$totalCompletedEstimate = 0; $totalWorkedHours = 0; $totalRemainingHours = 0; $totalStories = 0; $totalCompletedStories = 0;
        $totalSubtasks = 0; $totalCompletedSubtasks = 0;
        $sprints = $this->user->sprints()->whereIn('sprint_assembla_id', $this->request_data['sprints'])->get();
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

            $sprintData = [
                'project_name' => $sprint->getProjectName(),
                'sprint_name'  => $sprint->name,
                'worked_hours' => $sprintTotalWorkedHours,
                'remaining_hours' => $sprintTotalRemainingHours,
                'stories' => $sprintTotalStories,
                'completed_stories' => $sprintTotalCompletedStories,
                'completed_stories_percentage' => $sprint->getPercentCompletedStories(),
                'subtasks' => $sprintTotalSubtasks,
                'completed_subtasks' => $sprintTotalCompletedSubtasks,
                'completed_subtasks_percentage' => $sprint->getPercentCompletedSubtasks(),
                'remaining_estimate' => $sprintRemainingEstimate,
                'remaining_estimate_percentage' => $remainingEstimatePercentage,
                'completed_estimate' => $sprintCompletedEstimate,
                'completed_estimate_percentage' => $sprint->getTotalCompletedEstimatePercentage(),
                'total_estimate' => $sprintTotalEstimate,
                'assembla_url' => "https://app.assembla.com/spaces/".$sprint->getProject()->wikiname ."/milestones/".$sprint->sprint_assembla_id,
            ];
            $reportArrayBody['sprints'][$sprint->sprint_assembla_id] = $sprintData;

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


            $totalData = [
                'worked_hours' => $totalWorkedHours,
                'remaining_hours' => $totalRemainingHours,
                'stories' => $totalStories,
                'completed_stories' => $totalCompletedStories,
                'completed_stories_percentage' => Helper::getPercentageValue($totalCompletedStories, $totalStories),
                'subtasks' => $totalSubtasks,
                'completed_subtasks' => $totalCompletedSubtasks,
                'completed_subtasks_percentage' => Helper::getPercentageValue($totalCompletedSubtasks, $totalSubtasks),
                'remaining_estimate' => $totalRemainingEstimate,
                'remaining_estimate_percentage' => $remainingEstimate,
                'completed_estimate' => $totalCompletedEstimate,
                'completed_estimate_percentage' => $totalCompletedEstimatePercentage,
                'total_estimate' => $totalEstimate,
            ];
            $reportArrayBody['total'] = $totalData;
        }




        $this->processed($reportArrayBody);
    }

    public function getNotificationMessage()
    {
       return 'Sprints Report was processed correctly';
    }

    public function getRequestDataFormatted()
    {
        return count($this->request_data['sprints']).' Milestones';
    }

    public function getView()
    {
        return SprintsReport::VIEW;
    }
}