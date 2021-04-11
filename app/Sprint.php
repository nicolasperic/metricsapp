<?php

namespace App;

use App\Jobs\SyncUser;
use App\Models\Metrics\Sprint\TicketsMetrics;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Sprint extends Model
{
    use HasFactory;

    const PLANNER_TYPE_NONE = 0;
    const PLANNER_TYPE_BACKLOG = 1;
    const PLANNER_TYPE_CURRENT = 2;

    protected $guarded = [];

    private $monthlyHours;
    private $weeklyHours;
    private $userHours;

    /** @var TicketsMetrics */
    private $ticketMetrics = null;

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
        return $this->belongsToMany(Ticket::class)->orderBy('estimate', 'DESC')->orderBy('number', 'DESC');
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

    public function scopeOpen($query)
    {
        return $query->where('is_active', 1);
        //return $query->whereNotNull('completed_at')->where('state', self::CLOSED_STATE);//el ticket 385 estaba en delivered state 0 pero sin fecha de completed_at
    }

    public function scopeCurrent($query)
    {
        return $query->where('planner_type', Sprint::PLANNER_TYPE_CURRENT);
    }

    public function scopeClosed($query)
    {
        return $query->where('is_active', 0);
        //return $query->whereNotNull('completed_at')->where('state', self::CLOSED_STATE);//el ticket 385 estaba en delivered state 0 pero sin fecha de completed_at
    }

    public function getProjectName()
    {
        $project = $this->projects->first();
        if ($project !== null) {
            return $project->name;
        }

        return '';
    }

    public function getProject()
    {
        return $this->projects->first();
    }

    public function getFormattedPlannerType()
    {//TODO this function could easily go to a Helper (how can we use a helper on blade?)
        //0 None, 1 Backlog, 2 Current
        $plannerType = '';
        if ($this->planner_type == SPRINT::PLANNER_TYPE_BACKLOG) {
            $plannerType = '<span class="planner-type backlog">Backlog</span>';
        } else if ($this->planner_type == SPRINT::PLANNER_TYPE_CURRENT) {
            $plannerType = '<span class="planner-type current">Current</span>';
        }

        return $plannerType;
    }

    public function isCurrent()
    {
        return $this->planner_type == SPRINT::PLANNER_TYPE_CURRENT;
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
     * This function returns open tickets that are not subtasks for carry over
     * The sprint iteration process uses this data when creating a new milestone,
     * carry over will be assigned from the closed milestone
     *
     * @return mixed
     */
    public function getOpenTicketsForCarryOver()
    {
        return $this->tickets()
            ->where('hierarchy_type', '!=', Ticket::HIERARCHY_SUBTASK)
            ->where('state','=', Ticket::OPEN_STATE)->get();
    }

    /**
     * @return TicketsMetrics
     */
    public function getTicketsMetricsInstance()
    {
        if ($this->ticketMetrics === null) {
            $this->ticketMetrics = new TicketsMetrics($this);
        }
        return $this->ticketMetrics;
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
            'BA' => ['main' => '#66e0ff', 'hover' => '#008fb3'],//celeste
            'QA' => ['main' => '#ff9933', 'hover' => '#cc6600'],//naranja
            'Design-UX' => ['main' => '#ff99ff', 'hover' => '#ff4dff']//violeta más claro
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

        $ticketsMetrics = $this->getTicketsMetricsInstance();
        $storiesCount = $ticketsMetrics->getStoriesCount();
        $totalWorkedHours = $ticketsMetrics->getTotalWorkedHours();

        //dd($types);
        $result = array();
        foreach ($typesUsCount as $type) {
            $label = ($type->type)? $type->type: 'Empty';

            $countPercentage = (floatval($storiesCount) !== 0.0 )?number_format(($type->total / $storiesCount) * 100, 2):0;

            $result[$label] = [
                'label' => $label,
                'total' => $type->total,
                'count_percentage' => $countPercentage,
                'total_invested_hours' => 0,
                'hours_percentage' => 0,
                'color' => (array_key_exists($label, $colors))?$colors[$label]: ['main' => '#ABC123', 'hover' => '#ABC123'],//TODO generate random colors
            ];
        }



        foreach ($typesHours as $type) {
            $label = ($type->type)? $type->type: 'Empty';

            $hoursPercentage = (floatval($totalWorkedHours) !== 0.0 )? number_format(($type->total_invested_hours / $totalWorkedHours) * 100, 2) : 0;

            if (array_key_exists($label, $result)) {
                $result[$label] = array_merge($result[$label],[
                    'total_invested_hours' => $type->total_invested_hours,
                    'hours_percentage' => $hoursPercentage,
                ]);
            }

        }


        return $result;
    }

    public function getTimeReport()
    {
        $this->weeklyHours = array();//week => total XXX, users [ foco => hs]
        $this->monthlyHours = array();//month => total XY, users => [ foco => hs]
        $this->userHours = array();//user_id => hours, tasks

        $users = [];
        $this->projects[0]->assemblaUsers->map( function ($assemblaUser) use (&$users) {
            $users[$assemblaUser->user_assembla_id] = ['name' => $assemblaUser->name, 'picture' => $assemblaUser->picture];
        });//->toArray();//->pluck('name', 'user_assembla_id')->toArray();//eager loaded

        $tickets = $this->tickets;//eager loaded

        foreach ($this->tickets as $ticket) {
            //$ticketTimes = TicketTime::where('ticket_assembla_id', $ticket->ticket_assembla_id)->get();

            foreach ($ticket->ticketTimes as $ticketTime) {
                $this->_trackTime($ticketTime, $users, $tickets);
            }


        }



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
                SyncUser::dispatch(Auth::user(), $ticketTime->user_assembla_id, $this->projects[0]);
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

    //TODO this function and all the reporting logic needs to be on a different class
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

    }
}

