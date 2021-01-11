<?php

namespace App\Importer;

use App\Dto\Mapper\ProjectMapper;
use App\Dto\ProjectDto;
use App\Integration\AssemblaGateway;
use App\Project;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProjectImporter
{

    /**
     *
     */
    public function importAllAssemblaSpacesAsProjects(User $user)
    {
        Log::info('[Projects Importer] Starting import process');
        $allProjectsFromAPI = array();
        $startTime = time();

        $assemblaGateway = new AssemblaGateway($user);
        $spaces = $assemblaGateway->getSpaces();

        $APIEndTime = time();
        $minutes = round(($APIEndTime - $startTime)/60, 2);
        Log::info('[Projects Importer] API response time '.$minutes.' minutes');

        if ($spaces) {
            /** @var ProjectDto $projectDto */
            foreach ($spaces as $projectDto) {
                $allProjectsFromAPI[$projectDto->getProjectAssemblaId()] = true;
                if (!Project::projectExists($projectDto->getProjectAssemblaId())) {
                    ProjectMapper::createProjectFromDTO($projectDto);
                } else {
                    /** @var Project $project */
                    $project = Project::getProjectByAssemblaId($projectDto->getProjectAssemblaId());

                    ProjectMapper::updateProjectFromDTO($project, $projectDto);//Importer syncing project data

                    //if project already exists it might already have sprints so we need to add those sprints to the logged user
                    foreach ($project->sprints as $sprint) {
                        if (!Auth::user()->hasSprint($sprint->sprint_assembla_id)) {
                            $this->_addSprintToUser($sprint);
                        }
                    }
                }

                if (!Auth::user()->hasProject($projectDto->getProjectAssemblaId())) {
                    $this->_addProjectToUser($projectDto);
                }
            }

            //sync milestones received from API with project->sprints
            foreach (Auth::user()->projects as $project) {
                if (!array_key_exists($project->project_assembla_id, $allProjectsFromAPI)) {
                    Auth::user()->projects()->detach($project->id);
                }
            }
        }

        $endtime = time();
        $minutes = round(($endtime - $startTime)/60, 2);
        Log::info('[Projects Importer] Finished in '.$minutes.' minutes');
    }

    private function _addProjectToUser($projectDto)
    {
        $project = Project::getProjectByAssemblaId($projectDto->getProjectAssemblaId());
        Auth::user()->projects()->save($project);
    }

    private function _addSprintToUser($sprint)
    {
        Auth::user()->sprints()->save($sprint);
    }

}
