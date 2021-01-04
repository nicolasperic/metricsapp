<?php

namespace App\Importer;

use App\Dto\Mapper\SprintMapper;
use App\Dto\SprintDto;
use App\Integration\AssemblaGateway;
use App\Sprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SprintImporter
{

    /**
     *
     */
    public function importProjectMilestonesAsSprints($project)
    {
        Log::info('[Milestones Importer] Starting import process');
        $startTime = time();

        $assemblaGateway = new AssemblaGateway();
        $sprints = $assemblaGateway->getMilestonesForSpace($project->wikiname);

        $APIEndTime = time();
        $minutes = round(($APIEndTime - $startTime)/60, 2);
        Log::info('[Milestones Importer] API response time '.$minutes.' minutes');

        if ($sprints !== false) {
            foreach ($sprints as $sprintDto) {
                if (!Sprint::sprintExists($sprintDto->getSprintAssemblaId())) {
                    $sprint = SprintMapper::createSprintFromDTO($sprintDto);
                    $this->_addSprintToProject($sprint, $project);
                } else {
                    $sprint = Sprint::getSprintByAssemblaId($sprintDto->getSprintAssemblaId());
                    SprintMapper::updateSprintFromDTO($sprint, $sprintDto);
                }

                if (!Auth::user()->hasSprint($sprintDto->getSprintAssemblaId())) {
                    $this->_addSprintToUser($sprint);
                }
            }
        }
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
