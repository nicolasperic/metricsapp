<?php

namespace App\Models\Metrics\Sprint;

use App\Sprint;
use App\Ticket;

/**
 * Class TicketsMetrics
 *
 * This class has the responsibility of calculating sprint metrics based on tickets
 *
 * Functions names convention:
 * - getTotalVariable will return a sum of the required variable (i.e getTotalWorkedHours)
 * - getVariableCount will return a count i.e getTicketsCount (returns the total amount of tickets)
 *
 * @package App\Models\Metrics\Sprint
 */

class TicketsMetrics {


    /**
     * @var float sum of worked hours for all ticket in sprint
     */
    private $totalWorkedHours;
    /**
     * @var float sum of working hours for all tickets in sprint
     */
    private $totalWorkingHours;
    /**
     * @var float sum of estimate for completed tickets in the sprint
     */
    private $totalCompletedEstimate;
    /**
     * @var float sum of estimate for all tickets in the sprint
     */
    private $totalEstimate;
    /**
     * @var int number of tickets in the sprint
     */
    private $allTicketsCount;
    /**
     * @var int number of subtasks in the sprint
     */
    private $subtasksCount;
    /**
     * @var int number of stories in the sprint
     */
    private $storiesCount;
    /**
     * @var int number of completed tickets in the sprint
     */
    private $completedTicketsCount;
    /**
     * @var int number of completed stories in the sprint
     */
    private $completedStoriesCount;
    /**
     * @var int number of completed subtasks in the sprint
     */
    private $completedSubtasksCount;

    private $storiesWithoutEstimateCount;

    /**
     * @var Sprint
     */
    private $sprint;

    function __construct(Sprint $sprint)
    {
        $this->sprint = $sprint;
    }

    private function tickets()
    {
        return $this->sprint->tickets();
    }

    /**
     * This function returns the total amount of worked hours for the sprint
     * It's a calculated value by adding the worked_hours attribute for all tickets that belong to the sprint
     * This value represents the worked done on the sprint in hours (hours already spent)
     * @return mixed
     */
    public function getTotalWorkedHours()
    {
        if (!isset($this->totalWorkedHours)) {
            $this->totalWorkedHours = $this->tickets()->sum('worked_hours');
        }
        return $this->totalWorkedHours;
    }

    /**
     * This function returns the total amount of working hours for the sprint.
     * It's a calculated value by adding the working_hours attribute for all tickets that belong to the sprint.
     * This value represents the Remaining work based on hours for the Sprint.
     * @return mixed
     */
    public function getTotalWorkingHours()
    {
        if (!isset($this->totalWorkingHours)) {
            $this->totalWorkingHours = $this->tickets()->sum('working_hours');
        }
        return $this->totalWorkingHours;
    }

    /**
     * @return mixed
     */
    public function getTotalCompletedEstimate()
    {
        if (!isset($this->totalCompletedEstimate)) {
            $this->totalCompletedEstimate = $this->sprint->getCompletedTickets()->sum('estimate');
        }
        return $this->totalCompletedEstimate;
    }

    /**
     * @return mixed
     */
    public function getTotalEstimate()
    {
        if (!isset($this->totalEstimate)) {
            $this->totalEstimate = $this->tickets()->sum('estimate');
        }
        return $this->totalEstimate;
    }

    /**
     * @return mixed
     */
    public function getTotalRemainingEstimate()
    {
        return $this->getTotalEstimate() - $this->getTotalCompletedEstimate();
    }

    /**
     * @param int $decimals
     *
     * @return int|string
     */
    public function getTotalCompletedEstimatePercentage($decimals = 0)
    {
        if ($this->getTotalEstimate() == 0)
            return 0;
        return number_format(($this->getTotalCompletedEstimate() / $this->getTotalEstimate()) * 100, $decimals);
    }

    /**
     * @param int $decimals
     *
     * @return int|string
     */
    public function getTotalCompletedStoriesPercentage($decimals = 0)
    {
        $storiesCount = $this->getStoriesCount();
        if ($storiesCount == 0)
            return 0;

        return number_format(($this->getCompletedStoriesCount() / $storiesCount) * 100, $decimals);
    }

    /**
     * @param int $decimals
     *
     * @return int|string
     */
    public function getTotalCompletedSubtasksPercentage($decimals = 0)
    {
        $subtasksCount = $this->getSubtasksCount();
        if ($subtasksCount == 0)
            return 0;

        return number_format($this->getCompletedSubtasksCount() / $subtasksCount * 100, $decimals);

    }

    /**
     * This function returns the number of tickets on the sprint,
     * both stories and subtasks are considered
     *
     * @return mixed
     */
    public function getAllTicketsCount()
    {
        if (!isset($this->allTicketsCount)) {
            $this->allTicketsCount = $this->tickets()->count();
        }

        return $this->allTicketsCount;
    }

    /**
     * This function returns the total number of subtasks on the sprint
     * User stories are not considered on this calculation
     * @return mixed
     */
    public function getSubtasksCount()
    {
        if (!isset($this->subtasksCount)) {
            $this->subtasksCount = $this->tickets()->where('hierarchy_type', Ticket::HIERARCHY_SUBTASK)->count();
        }
        return $this->subtasksCount;
    }

    /**
     * This function returns the total number of user stories on the sprint
     * Subtasks are not considered on this calculation
     * @return mixed
     */
    public function getStoriesCount()
    {
        if (!isset($this->storiesCount)) {
            $this->storiesCount = $this->tickets()->where('is_story', true)->count();
        }
        return $this->storiesCount;
    }

    /**
     * This function returns the total number completed tickets
     * both subtasks and user stories are considered
     * @return mixed
     */
    public function getCompletedTicketsCount()
    {
        if (!isset($this->completedTicketsCount)) {
            $this->completedTicketsCount = $this->sprint->getCompletedTickets()->count();
        }
        return $this->completedTicketsCount;
    }

    /**
     * This function will return completed user stories count on the sprint
     *
     * @return mixed
     */
    public function getCompletedStoriesCount()
    {
        if (!isset($this->completedStoriesCount)) {
            $this->completedStoriesCount = $this->tickets()->where('is_story', true)->completed()->count();
        }
        return $this->completedStoriesCount;
    }

    /**
     * This function will return completed subtasks count on the sprint
     *
     * @return mixed
     */
    public function getCompletedSubtasksCount()
    {
        if (!isset($this->completedSubtasksCount)) {
            $this->completedSubtasksCount = $this->tickets()->where('is_story', false)->completed()->count();
        }
        return $this->completedSubtasksCount;
    }

    /**
     * Not used in any other place rather than tests
     * @return string
     */
    public function getAverageLeadTime()
    {
        $completedTickets = $this->sprint->getCompletedTickets();
        $totalTickets = $completedTickets->count();
        if ($totalTickets) {
            $totalLeadTime = 0;
            $completedTickets->each(function ($ticket) use (&$totalLeadTime) {
                $totalLeadTime += $ticket->getLeadTime();
            });

            return number_format($totalLeadTime/$totalTickets, 2);
        }
    }

    /**
     * Not used in other place rather than tests
     * @return string
     */
    public function getAverageCycleTime()
    {
        $completedTickets = $this->sprint->tickets()->started()->completed();
        $totalTickets = $completedTickets->count();
        if ($totalTickets) {
            $totalCycleTime = 0;
            $completedTickets->each(function ($ticket) use (&$totalCycleTime) {
                $totalCycleTime += $ticket->getCycleTime();
            });

            return number_format($totalCycleTime/$totalTickets, 2);
        }
    }

    //Following functions are for validations
    /**
     * This function returns the count of stories without estimate on the sprint
     *
     * @return mixed
     */
    public function getStoriesWithoutEstimateCount()
    {
        if (!isset($this->storiesWithoutEstimateCount)) {
            $this->storiesWithoutEstimateCount = $this->tickets()->where('estimate', 0)->where('is_story', true)->count();
        }
        return $this->storiesWithoutEstimateCount;
    }

    /**
     * This function will return the count of US with invalid subtasks statuses
     *
     * @return int
     */
    public function getStoriesWithInconsistentStateCount()
    {
        $completedUserStories= $this->tickets()->completed();
        $totalTickets = $completedUserStories->count();
        if ($totalTickets) {
            $totalInconsistentUserStories = 0;
            $completedUserStories->each(function ($ticket) use (&$totalInconsistentUserStories) {
                if (count($ticket->getInvalidStatusSubtasks()) > 0) {
                    $totalInconsistentUserStories ++;
                }
            });
            return $totalInconsistentUserStories;
        }
        return 0;
    }
}