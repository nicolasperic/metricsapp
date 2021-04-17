<?php

namespace App\Charts\Adapters;

use App\Charts\BarChart;
use App\Models\ProjectStat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ProjectBarChart
{
    /** @var  BarChart */
    private $barChart;

    public function generateHoursPerUserBarChartForCurrentMonth()
    {
        $date = Carbon::now();
        $month = $date->month;
        $year = $date->year;
        return $this->_generateHoursPerUserBarChartFor($month, $year);
    }

    public function generateHoursPerUserBarChartForLastMonth()
    {
        $date = Carbon::now()->subMonth();
        $month = $date->month;
        $year = $date->year;
        return $this->_generateHoursPerUserBarChartFor($month, $year);
    }

    /**
     *
     * @param $month
     * @param $year
     *
     * @return BarChart
     */
    private function _generateHoursPerUserBarChartFor($month, $year)
    {
        $starredProjects = Auth::user()->starredProjects;

        $olderUpdatedAt = false;
        $usersHours = [];
        foreach ($starredProjects as $project) {
            $projectStats = $this->getProjectStats($project, ProjectStat::MONTH_RANGE_TYPE, $month, $year);

            if ($projectStats !== null) {
                $projectUsersHours = json_decode($projectStats->users_hours, true);

                foreach ($projectUsersHours as $userAssemblaId => $userHours) {
                    if (array_key_exists($userAssemblaId, $usersHours)) {
                        $usersHours[$userAssemblaId]['total_hours'] += $userHours['total_hours'];
                    } else {
                        $usersHours[$userAssemblaId]['total_hours'] = $userHours['total_hours'];
                        $usersHours[$userAssemblaId]['label'] = $userHours['label'];
                    }
                }
                $updatedAt = $projectStats->updated_at;
                $olderUpdatedAt = ($olderUpdatedAt === false || $updatedAt < $olderUpdatedAt)? $updatedAt: $olderUpdatedAt;
            }
        }

        usort($usersHours, function ($a, $b){
            return $a['total_hours'] < $b['total_hours'];
        });



        $elementId = 'hoursPerUserBarChart'.$month.'_'.$year;
        $this->barChart = new BarChart('Hours per user', $elementId);


        $labels = [];
        $hoursValues = [];
        $hoursPercentage = [];

        $backgroundColors = [];
        $borderColors = [];

        foreach ($usersHours as $userHours) {
            $labels[] = $userHours['label'];
            $hoursValues[] = $userHours['total_hours'];
            //$hoursPercentage[] = $userHours['hours_percentage'];
            $color = $this->barChart->getNextColor();
            $backgroundColors[] = $this->barChart->adjustBrightness($color, 0.8);
            $borderColors[] = $color;
        }
        if (count($usersHours)) {
            $this->barChart->setHasInformation(true);
        }


        $this->barChart->setLabels($labels);
        $this->barChart->addDataset($this->createDataset('hours', $hoursValues, $backgroundColors, $borderColors));
        $this->barChart->setLastUpdated($projectStats->updated_at);
        $this->barChart->setWidth(4);

        return $this->barChart;
    }

    private function createDataset($label, $data, $backgroundColors, $borderColors)
    {
        $dataset = [
            'label' => $label,
            'data' => $data,
            'backgroundColor' => $backgroundColors,
            'borderColor' => $borderColors,
        ];

        return $dataset;
    }

    //TODO this function should be on a service related to ProjectStats NOT HERE!
    private function getProjectStats($project, $rangeType, $month, $year)
    {
        return $project->stats()
            ->where('range_type', $rangeType)
            ->orderBy('from_date')
            ->where('month', $month)
            ->where('year', $year)
            ->first();
    }
}