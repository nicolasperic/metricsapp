<?php

namespace App;

use App\Jobs\SprintIteration as SprintIterationJob;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SprintIteration extends Model
{
    use HasFactory;

    protected $guarded = [];

    const TWO_WEEKS = 2;
    const THREE_WEEKS = 3;
    const FOUR_WEEKS = 4;

    const ITERATION_STATUS_NOT_STARTED = 0;
    const ITERATION_STATUS_RUNNING = 1;
    const ITERATION_STATUS_STOPPED = 2;

    public static function createForProject($project)
    {
        return SprintIteration::create([
            'project_id' => $project->id,
            'iteration_status' => self::ITERATION_STATUS_NOT_STARTED,
        ]);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function isAutoIterationRunning()
    {
        return $this->iteration_status == self::ITERATION_STATUS_RUNNING;
    }

    public function start(User $user, $startDate, $currentSprintAssemblaId)
    {
        $sprint = Sprint::where('sprint_assembla_id', $currentSprintAssemblaId)->firstorFail();
        $endDate = $this->getNewMilestoneEndDate($startDate);

        //dd('start '.$startDate. ' end '.$endDate);

        $this->iteration_status = self::ITERATION_STATUS_RUNNING;
        $this->iteration_user_assembla_id = $user->user_assembla_id;
        $this->next_iteration_start_date = Carbon::parse($startDate)->addDays($this->sprint_duration * 7)->format('Y/m/d');
        $this->save();

        //SprintIterationJob::dispatch($user, $sprint, $startDate, $endDate);
    }

    public function iterate()
    {
        $this->increment('iterations_count');
        $this->next_iteration_start_date = Carbon::now()->addDays($this->sprint_duration * 7)->format('Y/m/d');
        $this->save();

        $sprint = $this->project->getCurrentSprint();
        $startDate = Carbon::now()->format('Y/m/d');
        $endDate = $this->getNewMilestoneEndDate($startDate);
        $user = User::where('user_assembla_id',$this->iteration_user_assembla_id)->first();

        //SprintIterationJob::dispatch($user, $sprint, $startDate, $endDate);
    }

    public function stop(User $user)
    {
        $this->iteration_status = self::ITERATION_STATUS_STOPPED;
        $this->iteration_user_assembla_id = $user->user_assembla_id;
        $this->save();
    }

    public function getStartedBy()
    {
        $user = false;

        if ($this->isAutoIterationRunning()) {
            $user = AssemblaUser::where('user_assembla_id',$this->iteration_user_assembla_id)->first();
            if ($user !== null) {
                return $user->name;
            }
        }

        return $user;
    }

    public function getNewMilestoneUniqueTitle($startDate)
    {
        $milestonePrefix = $this->sprint_prefix;
        $milestoneStartDate = Carbon::parse($startDate);
        $day = $milestoneStartDate->format('d');
        $month = strtoupper(substr($milestoneStartDate->format('F'), 0, 3));
        $year = $milestoneStartDate->format('y');

        return $milestonePrefix.$day.$month.$year;

    }

    public function getNewMilestoneStartDate($date, $next = false)
    {
        $startDate = Carbon::parse($date);
        $nextDays = ($next)? -7 : 0;

        $startWeekday = $this->sprint_start_weekday;

        return $startDate->subDays($startDate->dayOfWeek-$startWeekday+$nextDays)->format('Y/m/d');
    }

    /**
     * This function is used to calculate the possible start dates for a sprint iteration
     * If the start weekday is different from the current day of the week the user will have to choose between
     * i.e  Last Monday 2021/03/01 or Next Monday 2021/03/08
     * Returning the enclosing days
     *
     * If the $date day of week matches the selected date no option will be shown
     *
     * @param $date
     *
     * @return array|bool
     */
    public function getNewMilestoneStartDates($date)
    {
        $startDates = false;
        $weekday = [0 => 'Sunday', 1 => 'Monday',2 => 'Tuesday',3 => 'Wednesday',4 => 'Thursday',5 =>  'Friday',6 => 'Saturday'];

        $startDate = Carbon::parse($date);
        $startDayOfWeek = $startDate->dayOfWeek;
        $sprintStartWeekday = $this->sprint_start_weekday;

        if ($sprintStartWeekday != $startDayOfWeek && $sprintStartWeekday !== null) {
            if ($sprintStartWeekday > $startDayOfWeek) {
                $nextStartDate = $this->getNewMilestoneStartDate($date);
                $previousStartDate = Carbon::parse($nextStartDate)->subDays(7)->format('Y/m/d');
            } else {
                $nextStartDate = $this->getNewMilestoneStartDate($date, true);
                $previousStartDate = $this->getNewMilestoneStartDate($date);
            }
            $dayLabel = $weekday[$sprintStartWeekday];
            $last = 'Last '.$dayLabel.' '.$previousStartDate;
            $next = 'Next '.$dayLabel.' '.$nextStartDate;
            $startDates = [
                'last' => $last,
                'last_date' => $previousStartDate,
                'next' => $next,
                'next_date' => $nextStartDate,
            ];

        }

        return $startDates;
    }



    public function getNewMilestoneEndDate($startDate)
    {
        $weeks = $this->sprint_duration;
        $addDays = ($weeks * 7) - 1;
        $milestoneEndDate = Carbon::parse($startDate);
        return $milestoneEndDate->addDays($addDays)->format('Y/m/d');
    }
}
