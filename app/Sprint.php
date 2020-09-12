<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    protected $guarded = [];

    private $monthlyHours;
    private $weeklyHours;

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
        return $this->BelongsToMany(Ticket::class)->orderBy('story_points', 'DESC')->orderBy('number', 'DESC');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function getTotalWorkedHours()
    {
        return $this->tickets()->sum('worked_hours');
    }
    public function getTotalTickets()
    {
        return $this->tickets()->count();
    }

    public function getTotalSubtasks()
    {
        return $this->tickets()->where('is_story', false)->count();
    }

    public function getTotalStories()
    {
        return $this->tickets()->where('is_story', true)->count();
    }

    public function getCompletedTickets()
    {
        return $this->tickets()->completed();
    }

    public function getCompletedStories()
    {
        return $this->tickets()->where('is_story', true)->completed()->count();
    }

    public function getCompletedSubtasks()
    {
        return $this->tickets()->where('is_story', false)->completed()->count();
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

    public function getPercentCompletedStories($decimals = 0)
    {
        if ($this->getTotalStories() == 0)
            return 0;

        return number_format(($this->getCompletedStories() / $this->getTotalStories()) * 100, $decimals);
    }

    public function getPercentCompletedSubtasks($decimals = 0)
    {
        if ($this->getTotalSubtasks() == 0)
            return 0;

        return number_format($this->getCompletedSubtasks()/$this->getTotalSubtasks()*100, $decimals);

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

    public function getTimeReport()
    {
        $this->weeklyHours = array();//week => total XXX, users [ foco => hs]
        $this->monthlyHours = array();//month => total XY, users => [ foco => hs]
        foreach ($this->tickets as $ticket) {
            $ticketTimes = TicketTime::where('ticket_assembla_id', $ticket->ticket_assembla_id)->get();

            foreach ($ticketTimes as $ticketTime) {
                $this->_trackTime($ticketTime);
            }


        }

        ksort($this->weeklyHours);
        //print print_r($weeklyHours, 1).PHP_EOL;
        ksort($this->monthlyHours);
        //print print_r($monthlyHours, 1).PHP_EOL;
        //dd($this->monthlyHours);
        return array('weekly_hours' => $this->weeklyHours, 'monthly_hours' => $this->monthlyHours);
    }

    private function _trackTime($ticketTime)
    {
        $date = Carbon::parse($ticketTime->begin_at);
        $month = $date->month;
        $monday = $date->startOfWeek()->format('Y-m-d'); // monday
        $sunday = $date->endOfWeek()->format('Y-m-d');
        $weekOfYear = $date->weekOfYear;

        if (!array_key_exists($weekOfYear , $this->weeklyHours)) {
            //week data init
            $this->weeklyHours[$weekOfYear]['hours'] = 0;
            $this->weeklyHours[$weekOfYear]['taks'] = 0;
            $this->weeklyHours[$weekOfYear]['surr_monday'] = $monday;
            $this->weeklyHours[$weekOfYear]['surr_sunday'] = $sunday;
            $this->weeklyHours[$weekOfYear]['users'] = array();
            $this->weeklyHours[$weekOfYear]['tickets'] = array();
        }
        if (!array_key_exists($month, $this->monthlyHours)) {
            //month data init
            $this->monthlyHours[$month]['hours'] = 0;
            $this->monthlyHours[$month]['taks'] = 0;
            $this->monthlyHours[$month]['label'] = $date->format('F');
            $this->monthlyHours[$month]['users'] = array();
            $this->monthlyHours[$month]['tickets'] = array();
        }
        
        $this->_trackMonthlyHours($ticketTime, $month);
        $this->_trackWeeklyHours($ticketTime, $weekOfYear);

    }

    /*
        ej:
            4 =>    [
                hours => 7.25,
                tasks => 10,
                label => May,
                users => ['user_assembla_id' => ['hours'=> 2.25, 'tasks' => 3]],
                tickets => [
                        'number' => 1511,
                        'description'=>,
                        'hours'=>,
                        //['user_assembla_id' =>,]//este dato puede ser un array si varias personas trackean en el mismo ticket
                    ]
            ]
        */
    private function _trackMonthlyHours($ticketTime, $month)
    {
        $this->monthlyHours[$month]['hours'] += $ticketTime->hours;
        $this->monthlyHours[$month]['taks'] += 1;

        if (!array_key_exists($ticketTime->user_assembla_id, $this->monthlyHours[$month]['users'])) {
            //init user data
            $this->monthlyHours[$month]['users'][$ticketTime->user_assembla_id]['hours'] = 0;
            $this->monthlyHours[$month]['users'][$ticketTime->user_assembla_id]['tasks'] = 0;
            $this->monthlyHours[$month]['users'][$ticketTime->user_assembla_id]['tickets'] = array();
        }

        $this->monthlyHours[$month]['users'][$ticketTime->user_assembla_id]['hours'] += $ticketTime->hours;
        $this->monthlyHours[$month]['users'][$ticketTime->user_assembla_id]['tasks'] += 1;

        if (!array_key_exists($ticketTime->ticket_number, $this->monthlyHours[$month]['users'][$ticketTime->user_assembla_id]['tickets'])) {
            //init ticket data
            $this->monthlyHours[$month]['users'][$ticketTime->user_assembla_id]['tickets'][$ticketTime->ticket_number] = [
                'description' => $ticketTime->description,
                'hours' => 0,
            ];
        }
        if (!array_key_exists($ticketTime->ticket_number, $this->monthlyHours[$month]['tickets'])) {
            $ticket = Ticket::getTicketByAssemblaId($ticketTime->ticket_assembla_id);

            $parent = $ticket->parent();
            $parentLabel = '';
            if ($parent) {
                $parentLabel = $parent->number.' '.$parent->name;
            }
            $this->monthlyHours[$month]['tickets'][$ticketTime->ticket_number] = [
                'description' => $ticketTime->description,
                'hours' => 0,
                'parent' => $parentLabel,
            ];
        }

        $this->monthlyHours[$month]['users'][$ticketTime->user_assembla_id]['tickets'][$ticketTime->ticket_number]['hours'] += $ticketTime->hours;
        $this->monthlyHours[$month]['tickets'][$ticketTime->ticket_number]['hours'] += $ticketTime->hours;

    }

    private function _trackWeeklyHours($ticketTime, $weekOfYear)
    {
        $this->weeklyHours[$weekOfYear]['hours'] += $ticketTime->hours;
        $this->weeklyHours[$weekOfYear]['taks'] += 1;

        if (array_key_exists($ticketTime->user_assembla_id, $this->weeklyHours[$weekOfYear])) {
            $this->weeklyHours[$weekOfYear]['users'][$ticketTime->user_assembla_id]['hours'] += $ticketTime->hours;
            $this->weeklyHours[$weekOfYear]['users'][$ticketTime->user_assembla_id]['tasks'] += 1;
        } else {
            $this->weeklyHours[$weekOfYear]['users'][$ticketTime->user_assembla_id]['hours'] = $ticketTime->hours;
            $this->weeklyHours[$weekOfYear]['users'][$ticketTime->user_assembla_id]['tasks'] = 1;
        }
    }





    /*{
        foreach ($this->tickets as $ticket) {

            $ticketTimes = TicketTime::where('ticket_assembla_id', $ticket->ticket_assembla_id)->get();
            foreach ($ticketTimes as $ticketTime) {
                dd($ticketTime->ticket_number . ' ' . $ticketTime->hours . ' ' . $ticketTime->begin_at);
            }
            //TODO armar horas por (mes, o semana)
/*
 que te muestre las horas insumidas en los tickets por mes. Ej: Julio 34 hs; Junio 210 hs
> el sprint podría tirar horas por persona; mostrando por semana y abajo el total
ej:
            w1(13 al 19)    w2(20 al 26)    w3 (26 a hoy/)
Foco        28  			27		10
Jona        40			40		16
Nico         1			5		2
            69 (+3% respecto av)			72

             *
             *
             */
            //dd($ticketTimes->count());
            //dd($ticketTime->number+ ' '+$ticketTime->begin_at);
        //}
    //}
}

