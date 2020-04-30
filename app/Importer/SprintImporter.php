<?php

namespace App\Importer;

use App\Dto\SprintDto;
use App\Integration\AssemblaGateway;
use App\Sprint;
use Illuminate\Support\Facades\Auth;

class SprintImporter
{

    /**
     *
     */
    public function importProjectMilestonesAsSprints($project)
    {
        $assemblaGateway = new AssemblaGateway();
        $response = $assemblaGateway->getMilestonesForSpace($project->wikiname);

        if ($response->getStatusCode() == 200) {
            $result = json_decode($response->getBody()->getContents(), 1);
            foreach ($result as $milestoneData) {
                $sprintDto = new SprintDto($milestoneData);

                if (!Sprint::sprintExists($sprintDto->getSprintAssemblaId())) {
                    $this->_createSprintFromDTO($sprintDto, $project);
                } elseif (!Auth::user()->hasSprint($sprintDto->getSprintAssemblaId())) {
                    $sprint = Sprint::getSprintByAssemblaId($sprintDto->getSprintAssemblaId());
                    $this->_addSprintToUser($sprint);
                }
            }
        }
    }

    private function _createSprintFromDTO(SprintDto $sprintDto, $project)
    {
        $sprint = Sprint::create([
            'name' => $sprintDto->getTitle(),
            'sprint_assembla_id' => $sprintDto->getSprintAssemblaId(),
            'project_assembla_id' => $sprintDto->getProjectAssemblaId(),
            'is_active' => $sprintDto->getStatus(),
        ]);
        $this->_addSprintToUser($sprint);
        $this->_addSprintToProject($sprint, $project);
    }

    private function _addSprintToUser($sprint)
    {
        Auth::user()->sprints()->save($sprint);
    }

    private function _addSprintToProject($sprint, $project)
    {
        $project->sprints()->save($sprint);
    }

}
