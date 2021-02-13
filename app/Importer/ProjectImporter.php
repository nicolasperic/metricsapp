<?php

namespace App\Importer;

use App\Dto\Mapper\ProjectMapper;
use App\Dto\ProjectDto;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use App\Project;
use App\User;
use Illuminate\Support\Facades\Log;

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
}
