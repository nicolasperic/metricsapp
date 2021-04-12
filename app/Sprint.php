<?php

namespace App;

use App\Jobs\SyncUser;
use App\Models\Metrics\Sprint\TasksMetrics;
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

    /** @var TicketsMetrics */
    private $ticketsMetrics = null;

    /** @var TasksMetrics */
    private $tasksMetrics= null;

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
     * @return TicketsMetrics
     */
    public function getTicketsMetricsInstance()
    {
        if ($this->ticketsMetrics === null) {
            $this->ticketsMetrics = new TicketsMetrics($this);
        }
        return $this->ticketsMetrics;
    }

    /**
     * @return TasksMetrics
     */
    public function getTasksMetricsInstance()
    {
        if ($this->tasksMetrics === null) {
            $this->tasksMetrics = new TasksMetrics($this);
        }

        return $this->tasksMetrics;
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


}

