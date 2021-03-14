<?php

namespace App;

use App\Exceptions\MilestoneNotCreatedException;
use App\Exceptions\SprintIterationException;
use App\Jobs\SprintIteration as SprintIterationJob;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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

    const ERROR_NO_USER = 'Oops we were not able to find the required Assembla user to iterate the milestone. Iteration stopped.';
    const ERROR_NO_CURRENT_SPRINT = 'Oops it seems @wikiname has no current milestone. Iteration stopped.';

    public static function createForProject($project)
    {
        return SprintIteration::create([
            'project_id' => $project->id,
            'iteration_status' => self::ITERATION_STATUS_NOT_STARTED,
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return bool
     */
    public function isAutoIterationRunning()
    {
        return $this->iteration_status == self::ITERATION_STATUS_RUNNING;
    }

    public function start(User $user, $startDate)
    {
        $this->iteration_status = self::ITERATION_STATUS_RUNNING;
        $this->iteration_user_assembla_id = $user->user_assembla_id;
        $this->next_iteration_start_date = Carbon::parse($startDate)->addDays($this->sprint_duration * 7)->format('Y/m/d');
        $this->save();

        //$this->iterate();//DISPATCH AN ITERATION JOB! The start function gets called from a controller watch out!
    }

    public function iterate()
    {
        $sprint = $this->project->getCurrentSprint();
        $startDate = Carbon::now()->format('Y/m/d');
        $endDate = $this->getNewMilestoneEndDate($startDate);
        $user = User::where('user_assembla_id',$this->iteration_user_assembla_id)->first();

        $this->validateRequired($user, $sprint);

        try {
            $iterationProcess = new \App\Importer\SprintIteration($user);//how we could generate the sprint iteration?//maybe without an user and use the setUser function
            $newSprint = $iterationProcess->closeCurrentSprintAndCreateNewOneWithCarryOver($sprint, $startDate, $endDate);
            $this->setNextIterationData();//incrementing iterations count and setting next iteration date

        } catch(MilestoneNotCreatedException $e) {
            $this->stopWithError($e->getMessage());
            throw new SprintIterationException($e->getMessage());//encapsulating exception
        } catch (\Exception $e) {
            Log::info('[Sprint Iteration Exception] for '.$this->project->wikiname.' '.$e->getMessage());
            Log::info($e->getTraceAsString());
            $errorMessage = 'An error occurred when iterating sprint for '.$this->project->wikiname;
            $this->stopWithError($errorMessage);
            throw new SprintIterationException($errorMessage);
        }

        return $newSprint;
    }

    protected function stopWithError($errorMessage)
    {
        $this->next_iteration_start_date = null;
        $this->iteration_status = self::ITERATION_STATUS_STOPPED;
        $this->error_message = $errorMessage;
        $this->save();
    }

    public function stop(User $user)
    {
        $this->iteration_status = self::ITERATION_STATUS_STOPPED;
        $this->iteration_user_assembla_id = $user->user_assembla_id;
        $this->save();
    }

    /**
     * This function returns the name of the user that started the iteration
     * It's only used on the Iteration Settings page for information
     * @return bool|string user name
     */
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

    /**
     * This function will generate the unique title for the new milestone
     * Since Assembla Milestone titles are unique we use the defined milestone prefix + a date based content
     * i.e: with SE - as prefix we would generate SE - 12FEB21
     * @return string
     */
    public function getNewMilestoneUniqueTitle()
    {
        $startDate = Carbon::today();

        $milestonePrefix = $this->sprint_prefix;
        $milestoneStartDate = Carbon::parse($startDate);
        $day = $milestoneStartDate->format('d');
        $month = strtoupper(substr($milestoneStartDate->format('F'), 0, 3));
        $year = $milestoneStartDate->format('y');

        return $milestonePrefix.$day.$month.$year;

    }

    /**
     * This function returns the start date based on the received date and next parameter
     * Next parameter is used to return the following week day (+7 days added if next is true)
     *
     * The date param is used to calculate the start date based onthe sprint start weekday
     *
     * @param      $date
     * @param bool $next
     *
     * @return string
     */
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
     * //TODO this function could be moved to a helper sin is not using information from the SprintIteration instance
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

    /**
     * This function will handle iteration data updates after a new iteration is successfully done
     * iterations_count is increased by one and the next iteration start date is calculated
     */
    private function setNextIterationData()
    {
        $this->increment('iterations_count');
        $this->next_iteration_start_date = Carbon::now()->addDays($this->sprint_duration * 7)->format('Y/m/d');
        $this->save();
    }

    /**
     * This function will validate required user and sprint objects were found
     * If one of those is missing we throw an exception since the process won't be able to
     * @param $user
     * @param $sprint
     *
     * @throws SprintIterationException
     */
    private function validateRequired($user, $sprint)
    {
        if ($user === null || $sprint === null) {
            $userErrorMessage = self::ERROR_NO_USER;
            $sprintErrorMessage = str_replace('@wikiname',$this->project->wikiname, self::ERROR_NO_CURRENT_SPRINT);

            $errorMessage = ($sprint === null)? $sprintErrorMessage : $userErrorMessage;
            Log::info('[Sprint Iteration] '.$errorMessage);
            $this->stopWithError($errorMessage);
            throw new SprintIterationException($errorMessage);
        }



    }
}
