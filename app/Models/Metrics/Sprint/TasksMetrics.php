<?php

namespace App\Models\Metrics\Sprint;


use App\Sprint;

class TasksMetrics {

    private $monthlyHours;
    private $weeklyHours;
    private $userHours;


    /**
     * @var Sprint
     */
    private $sprint;

    function __construct(Sprint $sprint)
    {
        $this->sprint = $sprint;
    }
}