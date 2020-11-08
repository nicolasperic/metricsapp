<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Sprint extends Model
{
    protected $guarded = [];

    private $monthlyHours;
    private $weeklyHours;

    /**
     * This function will validate if there's a sprint matching the received assembla ID
     *
     * @param $assemblaId
     *
     * @return mixed
     */
    public static function sprintExists($assemblaId)
    {
        return self::where('sprint_assembla_id', $assemblaId)->exists();
    }

    /**
     * This function will return a sprint by assembla ID
     *
     * @param $sprintAssemblaId
     *
     * @return mixed
     */
    public static function getSprintByAssemblaId($sprintAssemblaId)
    {
        return self::where('sprint_assembla_id', $sprintAssemblaId)->first();
    }

    /**
     * This function returns all the tickets associated to the sprint
     *
     * @return $this
     */
    public function tickets()
    {
        return $this->belongsToMany(Ticket::class)->orderBy('story_points', 'DESC')->orderBy('number', 'DESC');
    }

    /**
     * This function returns all the projects a sprint belongs to
     * Assembla only allows a milestone/sprint to belong to one project/space.
     * We built a more flexible sprint thinking in joining many spaces to one major sprint
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function getProjectName()
    {
        if ($this->projects->first()) {
            return $this->projects->first()->name;
        }

        return '';
    }

    /**
     * This function will return the users that are assigned to the sprint
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * This function returns the total amount of worked hours for the sprint
     * It's a calculaed value by adding the worked_hours attribute for all tickets that belong to the sprint
     *
     * @return mixed
     */
    public function getTotalWorkedHours()
    {
        return $this->tickets()->sum('worked_hours');
    }

    /**
     * This function returns the total amount of invested hours for the sprint
     * It's a calculaed value by adding the total_invested_hours attribute for all tickets that belong to the sprint
     *
     * @return mixed
     */
    public function getTotalInvestedHours()
    {
        return $this->tickets()->sum('total_invested_hours');
    }

    /**
     * This function returns the total amount of tickets on the sprint,
     * both stories and subtasks are considered
     *
     * @return mixed
     */
    public function getTotalTickets()
    {
        return $this->tickets()->count();
    }

    /**
     * This function returns the total amount of subtasks on the sprint
     * User stories are not considered on this calculation
     * @return mixed
     */
    public function getTotalSubtasks()
    {
        return $this->tickets()->where('is_story', false)->count();
    }

    /**
     * This function returns the total amount of user stories on the sprint
     * Subtasks are not considered on this calculation
     * @return mixed
     */
    public function getTotalStories()
    {
        return $this->tickets()->where('is_story', true)->count();
    }

    /**
     * This function will return completed tickets on the sprint
     * Both user stories and subtasks
     * A ticket is considered complete when the state is 0
     *
     * @return mixed
     */
    public function getCompletedTickets()
    {
        return $this->tickets()->completed();
    }

    /**
     * This function will return completed user stories on the sprint
     *
     * TODO ticket function is not consistent with the return value Count!
     * @return mixed
     */
    public function getCompletedStories()
    {
        return $this->tickets()->where('is_story', true)->completed()->count();
    }

    /**
     * This function will return completed subtasks on the sprint
     *
     * TODO ticket function is not consistent with the return value Count!
     * @return mixed
     */
    public function getCompletedSubtasks()
    {
        return $this->tickets()->where('is_story', false)->completed()->count();
    }

    //TODO ticket function is not consistent with the return value Count!
    public function getUserStoriesWithoutStoryPoints()
    {
        return $this->tickets()->where('story_points', 0)->where('is_story', true)->count();
    }

    /**
     * This function will return the number of US with invalid subtasks statuses
     *
     * TODO este ticket devuelve un count y no se entiende con el nombre de la fn
     * @return int
     */
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

    /**
     * Returns the total amount of completed story points
     *
     * TODO este ticket devuelve un count y no se entiende con el nombre de la fn
     * @return mixed
     */
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

    /**
     * This function will return information grouped by User Story TYPE
     * Support, Bug, Requirement, Spike, Recurrent, Empty (when not assigned)
     * @return array
     */
    public function getUserStoriesTypePercentages()
    {

        $colors = [
            'Requirement' => ['main' => '#1cc88a', 'hover' => '#17a673'],//verde
            'Support' => ['main' => '#4e73df', 'hover' => '#3b5399'],//azul
            'Bug' => ['main' => '#e74a3b', 'hover' => '#c22819'],//rojo
            'Spike' => ['main' => '#f6c23e', 'hover' => '#cea334'],//amarillo
            'Recurrent' => ['main' => '#dbd8ce', 'hover' => '#bdbab1'],//gris
            'Empty' => ['main' => '#a947c4', 'hover' => '#8d3ba3'],//violeta
        ];
        $typesUsCount= $this->belongsToMany(Ticket::class)//DB::table('tickets')
            ->select('type', DB::raw('count(*) as total'))
            ->where('is_story', true)
            ->groupBy('type')
            ->get();


        $typesHours = $this->belongsToMany(Ticket::class)//DB::table('tickets')
            ->select('type', DB::raw('sum(worked_hours) as total_invested_hours'))
            //->where('is_story', false)
            ->groupBy('type')
            ->get();

        $totalStories = $this->getTotalStories();
        $totalWorkedHours = $this->getTotalWorkedHours();

        //dd($types);
        $result = array();
        foreach ($typesUsCount as $type) {
            $label = ($type->type)? $type->type: 'Empty';

            $countPercentage = (floatval($totalStories) !== 0.0 )?number_format(($type->total / $totalStories) * 100, 2):0;

            $result[$label] = [
                'label' => $label,
                'total' => $type->total,
                'count_percentage' => $countPercentage,
                'total_invested_hours' => 0,
                'hours_percentage' => 0,
                'color' => (array_key_exists($label, $colors))?$colors[$label]: '#ABC123',
            ];
        }

        foreach ($typesHours as $type) {
            $label = ($type->type)? $type->type: 'Empty';

            $hoursPercentage = (floatval($totalWorkedHours) !== 0.0 )? number_format(($type->total_invested_hours / $totalWorkedHours) * 100, 2) : 0;
            $result[$label] = array_merge($result[$label],[
                'total_invested_hours' => $type->total_invested_hours,
                'hours_percentage' => $hoursPercentage,
            ]);
        }


        return $result;
    }

    public function getTypeColor($type)
    {
        $colors = [
            'Requirement' => ['main' => '#1cc88a', 'hover' => '#17a673'],//verde
            'Support' => ['main' => '#4e73df', 'hover' => '#3b5399'],//azul
            'Bug' => ['main' => '#e74a3b', 'hover' => '#c22819'],//rojo
            'Spike' => ['main' => '#f6c23e', 'hover' => '#cea334'],//amarillo
            'Recurrent' => ['main' => '#dbd8ce', 'hover' => '#bdbab1'],//gris
            'Empty' => ['main' => '#a947c4', 'hover' => '#8d3ba3'],//violeta
        ];

        return $colors[$type]['main'];
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
        $monday = Carbon::parse($ticketTime->begin_at)->startOfWeek()->format('Y-m-d'); // monday
        $sunday = Carbon::parse($ticketTime->begin_at)->endOfWeek()->format('Y-m-d');
        $weekOfYear = $date->weekOfYear;

        if (!array_key_exists($weekOfYear , $this->weeklyHours)) {
            //week data init
            $this->weeklyHours[$weekOfYear]['hours'] = 0;
            $this->weeklyHours[$weekOfYear]['tasks'] = 0;
            $this->weeklyHours[$weekOfYear]['surr_monday'] = $monday;
            $this->weeklyHours[$weekOfYear]['surr_sunday'] = $sunday;
            $this->weeklyHours[$weekOfYear]['users'] = array();
            $this->weeklyHours[$weekOfYear]['tickets'] = array();
        }
        if (!array_key_exists($month, $this->monthlyHours)) {
            //month data init
            $this->monthlyHours[$month]['hours'] = 0;
            $this->monthlyHours[$month]['tasks'] = 0;
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
        $this->monthlyHours[$month]['tasks'] += 1;

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
        $this->weeklyHours[$weekOfYear]['tasks'] += 1;

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

