<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    public function tickets()
    {
        return $this->BelongsToMany(Ticket::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function getTotalTickets()
    {
        return $this->tickets()->count();
    }

    public function getCompletedTickets()
    {
        return $this->tickets()->completed();
    }

    public function getCompletedStoryPoints()
    {
        return $this->getCompletedTickets()->sum('story_points');
    }

    public function getTotalStoryPoints()
    {
        return $this->tickets()->sum('story_points');
    }

    public function getPercentCompletedStoryPoints()
    {
        if ($this->getTotalStoryPoints() == 0)
            return 0;

        return number_format(($this->getCompletedStoryPoints() / $this->getTotalStoryPoints()) * 100, 2);
    }

    public function getAverageLeadTime()
    {
        $completedTickets = $this->getCompletedTickets();
        $totalTickets = $completedTickets->count();
        if ($totalTickets) {
            $totalLeadTime = 0;
            $completedTickets->each(function ($ticket) use (&$totalLeadTime) {
                $totalLeadTime += $ticket->getLeadTime();
            });

            return number_format($totalLeadTime/$totalTickets, 2);
        }
    }

    public function getAverageCycleTime()
    {
        $completedTickets = $this->tickets()->started()->completed();
        $totalTickets = $completedTickets->count();
        if ($totalTickets) {
            $totalCycleTime = 0;
            $completedTickets->each(function ($ticket) use (&$totalCycleTime) {
                $totalCycleTime += $ticket->getCycleTime();
            });

            return number_format($totalCycleTime/$totalTickets, 2);
        }
    }
}

