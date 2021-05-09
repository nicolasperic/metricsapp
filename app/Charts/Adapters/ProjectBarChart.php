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
    
    public function generateHoursPerUserBarChartForSubMonths($subMonths)
    {
        $date = Carbon::now()->subMonths($subMonths);
        return $this->_generateHoursPerUserBarChartFor($date->month, $date->year);
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

                if (!isset($projectUsersHours) || count($projectUsersHours) == 0) {
                    continue;
                }

                foreach ($projectUsersHours as $userAssemblaId => $userHours) {
                    if (array_key_exists($userAssemblaId, $usersHours)) {
                        $usersHours[$userAssemblaId]['total_hours'] += $userHours['total_hours'];
                    } else {
                        $usersHours[$userAssemblaId]['total_hours'] = $userHours['total_hours'];
                        $usersHours[$userAssemblaId]['label'] = $userHours['label'];
                    }
                    $usersHours[$userAssemblaId][$project->wikiname] = $userHours['total_hours'];
                }
                $updatedAt = $projectStats->updated_at;
                if ($updatedAt)
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

        //a dataset per project with users hours keeping the same order//Elina, Nico, Foco, Barbi (de mayor a menor horas)

        /*
            datasets: [{
                label: 'SommierCenter',
                data: [48.5,0, 66],
                backgroundColor: "#4e73df",
            }
         */
        foreach ($usersHours as $userHours) {
            $labels[] = $userHours['label'];
        }
        foreach($starredProjects as $project) {
            $projectUsersHours = [];

            $color = $this->barChart->getNextColor();
            $borderColors = $this->barChart->adjustBrightness($color, -0.2);
            $backgroundColors = $color;
            foreach ($usersHours as $userHours) {

                $userHoursForProject = 0;
                if (array_key_exists($project->wikiname, $userHours)) {
                    $userHoursForProject = $userHours[$project->wikiname];
                }
                $projectUsersHours[] = $userHoursForProject;


                //$hoursValues[] = $userHours['total_hours'];
                //$hoursPercentage[] = $userHours['hours_percentage'];

            }

            $this->barChart->addDataset($this->createDataset($project->wikiname, $projectUsersHours, $backgroundColors, $borderColors));
        }


        if (count($usersHours)) {
            $this->barChart->setHasInformation(true);
            $this->barChart->setBarsCount(count($usersHours));
        }


        $this->barChart->setLabels($labels);


        $this->barChart->setLastUpdated($olderUpdatedAt);
        $this->barChart->setWidth(4);

        return $this->barChart;
    }

    /**
     *
     * @param $month
     * @param $year
     *
     * @return BarChart
     */
    private function __generateHoursPerUserBarChartFor($month, $year)
    {
        $starredProjects = Auth::user()->starredProjects;

        $olderUpdatedAt = false;
        $usersHours = [];
        foreach ($starredProjects as $project) {
            $projectStats = $this->getProjectStats($project, ProjectStat::MONTH_RANGE_TYPE, $month, $year);

            if ($projectStats !== null) {
                $projectUsersHours = json_decode($projectStats->users_hours, true);

                if (!isset($projectUsersHours) || count($projectUsersHours) == 0) {
                    continue;
                }

                foreach ($projectUsersHours as $userAssemblaId => $userHours) {
                    if (array_key_exists($userAssemblaId, $usersHours)) {
                        $usersHours[$userAssemblaId]['total_hours'] += $userHours['total_hours'];
                    } else {
                        $usersHours[$userAssemblaId]['total_hours'] = $userHours['total_hours'];
                        $usersHours[$userAssemblaId]['label'] = $userHours['label'];
                    }
                }
                $updatedAt = $projectStats->updated_at;
                if ($updatedAt)
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
        $this->barChart->setLastUpdated($olderUpdatedAt);
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
            ->where('month', $month)
            ->where('year', $year)
            ->first();
    }
}