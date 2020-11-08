<?php

namespace App\Importer;

use App\Dto\Mapper\ProjectMapper;
use App\Integration\AssemblaGateway;
use App\Project;
use Illuminate\Support\Facades\Auth;

class ProjectImporter
{

    /**
     *
     */
    public function importAllAssemblaSpacesAsProjects()
    {
        $assemblaGateway = new AssemblaGateway();
        $spaces = $assemblaGateway->getSpaces();

        if ($spaces) {
            foreach ($spaces as $projectDto) {
                if (!Project::projectExists($projectDto->getProjectAssemblaId())) {
                    ProjectMapper::createProjectFromDTO($projectDto);
                } elseif (!Auth::user()->hasProject($projectDto->getProjectAssemblaId())) {
                    $this->_addProjectToUser($projectDto);
                }
            }
        }
    }

    private function _addProjectToUser($projectDto)
    {
        $project = Project::getProjectByAssemblaId($projectDto->getProjectAssemblaId());
        Auth::user()->projects()->save($project);
    }

}
