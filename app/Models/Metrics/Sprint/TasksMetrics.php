<?php

namespace App\Models\Metrics\Sprint;


use App\Jobs\SyncUser;
use App\Sprint;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Class TasksMetrics
 *
 * This class gathers and organizes the Sprint metrics based on tracked time (tasks for Assembla)
 * Tracked time is classified by month, week and user
 *
 * @package App\Models\Metrics\Sprint
 */
class TasksMetrics {

    private $monthlyHours;
    private $weeklyHours;
    private $userHours;

    private $totalHours;
    private $totalTasks;


    /**
     * @var Sprint
     */
    private $sprint;

    function __construct(Sprint $sprint)
    {
        $this->sprint = $sprint;
    }

    public function getTimeReport()
    {
        $this->weeklyHours = array();//week => total XXX, users [ foco => hs]
        $this->monthlyHours = array();//month => total XY, users => [ foco => hs]
        $this->userHours = array();//user_id => hours, tasks

        $users = [];
        $this->sprint->projects[0]->assemblaUsers->map( function ($assemblaUser) use (&$users) {
            $users[$assemblaUser->user_assembla_id] = ['name' => $assemblaUser->name, 'picture' => $assemblaUser->picture];
        });//->toArray();//->pluck('name', 'user_assembla_id')->toArray();//eager loaded

        $tickets = $this->sprint->tickets;//eager loaded

        foreach ($this->sprint->tickets as $ticket) {
            //$ticketTimes = TicketTime::where('ticket_assembla_id', $ticket->ticket_assembla_id)->get();

            foreach ($ticket->ticketTimes as $ticketTime) {
                $this->totalHours += $ticketTime->hours;
                $this->totalTasks++;
                $this->_trackTime($ticketTime, $users, $tickets);
            }


        }

        //dd("Total hours $this->totalHours - Total tasks $this->totalTasks");


        ksort($this->weeklyHours);
        ksort($this->monthlyHours);
        ksort($this->userHours);
        $this->_trackUserHours();//this function uses the monthly hours data

        usort($this->userHours, function ($a,$b){
            return ($a['total_hours'] >= $b['total_hours']) ? -1 : 1;
        });

        return array(
            'weekly_hours' => $this->weeklyHours,
            'monthly_hours' => $this->monthlyHours,
            'user_hours' => $this->userHours
        );
    }

    private function _trackTime($ticketTime, $users, $tickets)
    {
        $date = Carbon::parse($ticketTime->begin_at);
        $month = $date->month;
        $monday = Carbon::parse($ticketTime->begin_at)->startOfWeek()->format('Y-m-d'); // monday
        $sunday = Carbon::parse($ticketTime->begin_at)->endOfWeek()->format('Y-m-d');
        $weekOfYear = $date->weekOfYear;
        $year = $date->year;

        if (!array_key_exists($year , $this->weeklyHours) ||  !array_key_exists($weekOfYear , $this->weeklyHours[$year])) {
            //week data init
            $this->weeklyHours[$year][$weekOfYear]['hours'] = 0;
            $this->weeklyHours[$year][$weekOfYear]['tasks'] = 0;
            $this->weeklyHours[$year][$weekOfYear]['surr_monday'] = $monday;
            $this->weeklyHours[$year][$weekOfYear]['surr_sunday'] = $sunday;
            $this->weeklyHours[$year][$weekOfYear]['users'] = array();
            $this->weeklyHours[$year][$weekOfYear]['tickets'] = array();
        }
        if (!array_key_exists($year, $this->monthlyHours) || !array_key_exists($month, $this->monthlyHours[$year])) {
            //month data init
            $this->monthlyHours[$year][$month]['hours'] = 0;
            $this->monthlyHours[$year][$month]['tasks'] = 0;
            $this->monthlyHours[$year][$month]['label'] = $date->format('F').' '.$date->format('y');
            $this->monthlyHours[$year][$month]['users'] = array();
            $this->monthlyHours[$year][$month]['tickets'] = array();

        }

        if (!array_key_exists($ticketTime->user_assembla_id, $this->userHours)) {
            //user data init
            $this->userHours[$ticketTime->user_assembla_id]['hours'] = [];
            $this->userHours[$ticketTime->user_assembla_id]['tasks'] = [];
            $this->userHours[$ticketTime->user_assembla_id]['total_hours'] = 0;
            $this->userHours[$ticketTime->user_assembla_id]['total_tasks'] = 0;


            $defaultPicture = 'https://assets3.assembla.com/assets/avatars/small/10-34646632626633326534663337306230663564393237353266396538633232383833626339353837396534323061616337666664633662376434376637303134.png';
            if (array_key_exists($ticketTime->user_assembla_id, $users)) {
                $userName = $users[$ticketTime->user_assembla_id]['name'];
                $picture = ($users[$ticketTime->user_assembla_id]['picture'])? $users[$ticketTime->user_assembla_id]['picture']: $defaultPicture;

            } else {
                $userName = 'Oops user deleted from space';
                Log::info('Dispatching user job for '.$ticketTime->user_assembla_id);
                SyncUser::dispatch(Auth::user(), $ticketTime->user_assembla_id, $this->sprint->projects[0]);
                $picture = $defaultPicture;

                //Adding not found user to array to prevent dispatching more than one job
                $users[$ticketTime->user_assembla_id] = [
                    'name' => $userName,
                    'picture' => $picture
                ];
            }

            $this->userHours[$ticketTime->user_assembla_id]['label'] = $userName;
            $this->userHours[$ticketTime->user_assembla_id]['picture'] = $picture;
        }

        $this->_trackMonthlyHours($ticketTime, $year, $month, $tickets);
        $this->_trackWeeklyHours($ticketTime, $year, $weekOfYear);


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

    private function _trackMonthlyHours($ticketTime, $year, $month, $tickets)
    {
        $this->monthlyHours[$year][$month]['hours'] += $ticketTime->hours;
        $this->monthlyHours[$year][$month]['tasks'] += 1;

        if (!array_key_exists($ticketTime->user_assembla_id, $this->monthlyHours[$year][$month]['users'])) {
            //init user data
            $this->monthlyHours[$year][$month]['users'][$ticketTime->user_assembla_id]['hours'] = 0;
            $this->monthlyHours[$year][$month]['users'][$ticketTime->user_assembla_id]['tasks'] = 0;
            $this->monthlyHours[$year][$month]['users'][$ticketTime->user_assembla_id]['tickets'] = array();
            $this->monthlyHours[$year][$month]['users'][$ticketTime->user_assembla_id]['label'] = $this->userHours[$ticketTime->user_assembla_id]['label'];

        }

        $this->monthlyHours[$year][$month]['users'][$ticketTime->user_assembla_id]['hours'] += $ticketTime->hours;
        $this->monthlyHours[$year][$month]['users'][$ticketTime->user_assembla_id]['tasks'] += 1;

        if (!array_key_exists($ticketTime->ticket_number, $this->monthlyHours[$year][$month]['users'][$ticketTime->user_assembla_id]['tickets'])) {
            //init ticket data
            $this->monthlyHours[$year][$month]['users'][$ticketTime->user_assembla_id]['tickets'][$ticketTime->ticket_number] = [
                'description' => $ticketTime->description,
                'hours' => 0,
            ];
        }
        if (!array_key_exists($ticketTime->ticket_number, $this->monthlyHours[$year][$month]['tickets'])) {
            //TODO tickets need to be loaded from the eager information
            //$ticket = Ticket::getTicketByAssemblaId($ticketTime->ticket_assembla_id);


//            $parent = $ticket->parent();
//            $parentLabel = '';
//            if ($parent) {
//                $parentLabel = $parent->number.' '.$parent->name;
//            }
            $this->monthlyHours[$year][$month]['tickets'][$ticketTime->ticket_number] = [
                'description' => $ticketTime->description,
                'hours' => 0,
//                'parent' => $parentLabel,
            ];
        }

        $this->monthlyHours[$year][$month]['users'][$ticketTime->user_assembla_id]['tickets'][$ticketTime->ticket_number]['hours'] += $ticketTime->hours;
        $this->monthlyHours[$year][$month]['tickets'][$ticketTime->ticket_number]['hours'] += $ticketTime->hours;

    }


    private function _trackWeeklyHours($ticketTime, $year, $weekOfYear)
    {
        $this->weeklyHours[$year][$weekOfYear]['hours'] += $ticketTime->hours;
        $this->weeklyHours[$year][$weekOfYear]['tasks'] += 1;

        if (array_key_exists($ticketTime->user_assembla_id, $this->weeklyHours[$year][$weekOfYear])) {
            $this->weeklyHours[$year][$weekOfYear]['users'][$ticketTime->user_assembla_id]['hours'] += $ticketTime->hours;
            $this->weeklyHours[$year][$weekOfYear]['users'][$ticketTime->user_assembla_id]['tasks'] += 1;
        } else {
            $this->weeklyHours[$year][$weekOfYear]['users'][$ticketTime->user_assembla_id]['hours'] = $ticketTime->hours;
            $this->weeklyHours[$year][$weekOfYear]['users'][$ticketTime->user_assembla_id]['tasks'] = 1;
        }
    }

    private function _trackUserHours()
    {
        foreach ($this->monthlyHours as $yearNumber => $months) {

            foreach ($months as $monthNumber => $monthHours) {

                foreach ($this->userHours as $userId => $userHour) {//$monthHours['users'] as $userId => $userHours) {
                    if (array_key_exists($userId, $monthHours['users'])) {
                        $this->userHours[$userId]['hours'][] = $monthHours['users'][$userId]['hours'];
                        $this->userHours[$userId]['tasks'][] = $monthHours['users'][$userId]['tasks'];
                        $this->userHours[$userId]['total_hours'] += $monthHours['users'][$userId]['hours'];
                        $this->userHours[$userId]['total_tasks'] += $monthHours['users'][$userId]['tasks'];
                    } else {
                        $this->userHours[$userId]['hours'][] = 0;
                        $this->userHours[$userId]['tasks'][] = 0;
                    }

                }
            }
        }

        foreach($this->userHours as $userId => $userData) {
            if ($this->totalHours) {
                $this->userHours[$userId]['hours_percentage'] = number_format($userData['total_hours']/$this->totalHours*100, 2);
            }

            if ($this->totalTasks) {
                $this->userHours[$userId]['tasks_percentage'] = number_format($userData['total_tasks']/$this->totalTasks*100, 2);
            }
        }

    }
}