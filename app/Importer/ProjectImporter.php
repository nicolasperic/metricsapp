<?php

namespace App\Importer;

use App\Dto\ProjectDto;
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
        $response = $assemblaGateway->getSpaces();

        if ($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody()->getContents(), 1);
            foreach ($result as $spaceData) {
                $projectDto = new ProjectDto($spaceData);

                if (!Project::projectExists($projectDto->getProjectAssemblaId())) {
                    $this->_createProjectFromDTO($projectDto);
                } elseif (!Auth::user()->hasProject($projectDto->getProjectAssemblaId())) {
                    $project = Project::getProjectByAssemblaId($projectDto->getProjectAssemblaId());
                    $this->_addProjectToUser($project);
                }
            }
        }
    }

    private function _createProjectFromDTO(ProjectDto $projectDto)
    {
        $project = Project::create([
            'name' => $projectDto->getName(),
            'code' => 'TPJ',
            'wikiname' => $projectDto->getWikiName(),
            'project_assembla_id' => $projectDto->getProjectAssemblaId(),
            'status' => $projectDto->getStatus(),
        ]);
        $this->_addProjectToUser($project);
    }

    private function _addProjectToUser($project)
    {
        Auth::user()->projects()->save($project);
    }

}
