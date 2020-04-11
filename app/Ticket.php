<?php

namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    public function sprints()
    {
        return $this->belongsToMany(Sprint::class);
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeStarted($query)
    {
        return $query->whereNotNull('started_at');
    }
    /**
     * Returns the number of days between creating and completing the task
     *
     * @return int days between created_at and completed_at
     */
    public function getLeadTime()
    {
        if ($this->completed_at) {
            return $this->_dateDiff($this->created_at, $this->completed_at);
        }
    }

    /**
     * Returns the number of days between starting and completing the task
     *
     * @return int days between started_at and completed_at
     */
    public function getCycleTime()
    {
        if ($this->completed_at && $this->started_at) {
            return $this->_dateDiff($this->started_at, $this->completed_at);
        }
    }

    /**
     * Secondary function that will calculate the amount of days between two dates
     * @param $startingDate string must be older than the ending date
     * @param $endingDate string must be newer than the starting date
     *
     * @return int
     */
    private function _dateDiff($startingDate, $endingDate)
    {
        $startingDateTime = new DateTime($startingDate);
        $endingDateTime = new DateTime($endingDate);
        if ($startingDateTime > $endingDateTime) {
            return;
        }

        return (int)$startingDateTime->diff($endingDateTime)->format('%a');//%a -> Total number of days
    }

}
