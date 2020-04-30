<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    protected $guarded = [];

    public static function sprintExists($assemblaId)
    {
        return self::where('sprint_assembla_id', $assemblaId)->exists();
    }

    public static function getSprintByAssemblaId($sprintAssemblaId)
    {
        return self::where('sprint_assembla_id', $sprintAssemblaId)->first();
    }

    public function tickets()
    {
        return $this->BelongsToMany(Ticket::class)->orderBy('story_points', 'DESC');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function getTotalInvestedHours()
    {
        return $this->tickets()->sum('total_invested_hours');
    }
    public function getTotalTickets()
    {
        return $this->tickets()->count();
    }

    public function getTotalStories()
    {
        return $this->tickets()->where('is_story', true)->count();
    }

    public function getCompletedTickets()
    {
        return $this->tickets()->completed();
    }

    public function getUserStoriesWithoutStoryPoints()
    {
        return $this->tickets()->where('story_points', 0)->where('is_story', true)->count();
    }

    public function getUserStoriesWithInconsistentState()
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

