<?php

namespace App\Importer;

use App\Dto\Mapper\ProjectMapper;
use App\Dto\ProjectDto;
use App\Dto\TicketTimeDto;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use App\Models\ProjectStat;
use App\Project;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Exception;

class ProjectImporter
{
    /**
     * @var User
     */
    private $user;
    /** @var AssemblaGateway  */
    private $assemblaGateway;

    /**
     * @param User $user
     */
    function __construct(User $user)
    {
        $this->user = $user;
        $this->assemblaGateway = new AssemblaGateway($this->user);
    }


    /**
     *
     */
    public function importAllAssemblaSpacesAsProjects()
    {
        Log::info('[Projects Importer] Starting import process');
        $allProjectsFromAPI = array();

        $page = 1;
        $queryParams = ['page' => $page];

        do {
            $startTime = time();
            $spaces = $this->assemblaGateway->getSpaces($queryParams);

            $APIEndTime = time();
            $minutes = round(($APIEndTime - $startTime)/60, 2);
            Log::info('[Projects Importer] API response time '.$minutes.' minutes');

            if ($spaces) {
                $queryParams['page'] = ++$page;
                /** @var ProjectDto $projectDto */
                foreach ($spaces as $projectDto) {
                    $allProjectsFromAPI[$projectDto->getProjectAssemblaId()] = true;
                    /** @var Project $project */
                    $project = Project::getProjectByAssemblaId($projectDto->getProjectAssemblaId());
                    if ($project === null) {
                        $project = ProjectMapper::createProjectFromDTO($projectDto);
                    } else {
                        $project = ProjectMapper::updateProjectFromDTO($project, $projectDto);//Importer syncing project data

                        //if project already exists it might already have sprints so we need to add those sprints to the logged user
                        foreach ($project->sprints as $sprint) {
                            if (!$this->user->hasSprint($sprint->sprint_assembla_id)) {
                                $this->user->sprints()->save($sprint);
                            }
                        }
                    }

                    if (!$this->user->hasProject($project->project_assembla_id)) {
                        $this->user->projects()->save($project);
                    }

                    $role = $this->assemblaGateway->getUserRoleInSpace($this->user->user_assembla_id, $project->wikiname);
                    if ($role !== false) {
                        $this->user->projects()->updateExistingPivot($project->id, $role);
                    }
                }
            } else {
                break;
            }
        } while(count($spaces) === AssemblaRequest::PER_PAGE);

        //sync milestones received from API with project->sprints
        foreach ($this->user->projects as $project) {
            if (!array_key_exists($project->project_assembla_id, $allProjectsFromAPI)) {
                $this->user->projects()->detach($project->id);
            }
        }

        $endtime = time();
        $minutes = round(($endtime - $startTime)/60, 2);
        Log::info('[Projects Importer] Finished in '.$minutes.' minutes');
    }

    public function calculateAndStoreProjectStatsFor($project, $year, $month)
    {
        //we need to calculate the start and end dates for the given month
        $firstDayOfMonth = Carbon::parse($year.'/'.$month.'/01')->startOfMonth()->format('Y/m/d H:i');
        $lastDayOfMonth = Carbon::parse($year.'/'.$month.'/01')->endOfMonth()->format('Y/m/d H:i');


        $totalHours = null;
        $totalTasks = null;

        try {
            $page = 1;
            do {
                $queryParams = [
                    'spaces' => $project->project_assembla_id,
                    'from'   => $firstDayOfMonth,
                    'to'     => $lastDayOfMonth,
                    'page'   => $page,
                ];

                $tasks = $this->assemblaGateway->getTasks($queryParams);
                if ($tasks) {
                    /** @var  TicketTimeDto $task */
                    foreach ($tasks as $task) {
                        $totalHours += $task->getHours();
                        $totalTasks++;

                    }
                }



                if (count($tasks) === AssemblaRequest::PER_PAGE) {
                    $page++;
                }
            } while(count($tasks) === AssemblaRequest::PER_PAGE);
        } catch(\Exception $e) {
            Log::info($e->getMessage());
        }


        //Here we will update the project stat
        $projectStat = ProjectStat::where('project_id', $project->id)->where('month', $month)->where('year', $year)->first();
        if ($projectStat == null) {

            ProjectStat::create([
                'project_id' => $project->id,
                'from_date' => $firstDayOfMonth,
                'to_date' => $lastDayOfMonth,
                'year' => $year,
                'month' => $month,
                'worked_hours' => $totalHours,
                'total_tasks' => $totalTasks,
                'range_type' => ProjectStat::MONTH_RANGE_TYPE
            ]);
        } else {

            $projectStat->from_date = $firstDayOfMonth;
            $projectStat->to_date = $lastDayOfMonth;
            $projectStat->worked_hours = $totalHours;
            $projectStat->total_tasks = $totalTasks;
            $projectStat->save();
        }

        Log::info("$project->wikiname hours: $totalHours tasks: $totalTasks for $year $month in $page pages");
    }
}
