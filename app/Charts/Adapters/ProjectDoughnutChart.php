<?php

namespace App\Charts\Adapters;

use App\Charts\DoughnutChart;
use App\Models\ProjectStat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ProjectDoughnutChart
{


    /** @var  DoughnutChart */
    private $doughnutChart;

    public function generateStarredProjectsCurrentMonthHoursChart()
    {
        $date = Carbon::now();
        $monthLabel = $date->monthName;
        $month = $date->month;
        $year = $date->year;
        $chartTitle = "Starred Projects Hours for current month ($monthLabel $year)";

        return $this->generateStarredProjectsMonthlyHoursChart($chartTitle, $month, $year);
    }

    public function generateStarredProjectsLastMonthHoursChart()
    {
        $date = Carbon::now()->subMonth();
        $monthLabel = $date->monthName;
        $month = $date->month;
        $year = $date->year;
        $chartTitle = "Starred Projects Hours for last month ($monthLabel $year)";

        return $this->generateStarredProjectsMonthlyHoursChart($chartTitle, $month, $year);
    }

    private function generateStarredProjectsMonthlyHoursChart($chartTitle = false, $month, $year)
    {
        $elementId = 'starredMonthlyHoursDoughnutChart_'.$month.'_'.$year;
        $this->doughnutChart = new DoughnutChart($chartTitle, $elementId);



        $starredProjects = Auth::user()->starredProjects;
        $hoursValues = [];
        $totalHours = 0;
        $labels = [];

        $olderUpdatedAt = false;
        foreach ($starredProjects as $project) {
            $labels[] = $project->wikiname;

            $projectStats = $this->getProjectStats($project, ProjectStat::MONTH_RANGE_TYPE, $month, $year);

            $hoursValues[] = $projectStats['hours'];
            $totalHours += $projectStats['hours'];

            if ($projectStats['updated_at'])
                $olderUpdatedAt = ($olderUpdatedAt === false || $projectStats['updated_at'] < $olderUpdatedAt)?$projectStats['updated_at']: $olderUpdatedAt;

            //$i++;
        }


        $this->doughnutChart->setLastUpdated($olderUpdatedAt);
        $this->doughnutChart->setLabels($labels);



        $colors = [];
        $data = [];
        foreach ($hoursValues as $hours) {

            $hoursPercentage = ($totalHours != 0) ? number_format($hours*100/$totalHours, 2)  : null;


            $data[] = $hoursPercentage;
            $colors[] = $this->doughnutChart->getNextColor();
        }

        $this->doughnutChart->addDataset($this->createDataset('Horas','horas', $data,$hoursValues, $colors));



        return $this->doughnutChart;
    }

    //TODO move this ProjectStats method to a ProjectStats service layer
    private function getProjectStats($project, $rangeType, $month, $year)
    {
        $hours = null;
        $updatedAt = null;

        $projectStat = $project->stats()
            ->where('range_type', $rangeType)
            ->orderBy('from_date')
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($projectStat !== null) {
            $this->doughnutChart->setHasInformation(true);
            $hours = $projectStat->worked_hours;
            $updatedAt = $projectStat->updated_at;
        }

        return [
            'hours' => $hours,
            'updated_at' => $updatedAt
        ];
    }

    private function createDataset($label, $dataLabel, $data, $hourValues, $color)
    {
        $dataset = [
            'label' => $label,
            'data' => $data,//percentages
            'backgroundColor' => $color,
            'hoverBackgroundColor' => $color,//how to handle this? similar color
            'hoverBorderColor' => "rgba(234, 236, 244, 1)",
            'realValues' => $hourValues,
            'dataLabel' => $dataLabel
        ];

        return $dataset;
    }
}