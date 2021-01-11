<?php

namespace App\Importer;

use App\Dto\Mapper\SprintMapper;
use App\Dto\SprintDto;
use App\Integration\AssemblaGateway;
use App\Sprint;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SprintImporter
{

    /**
     *
     */
    public function importProjectMilestonesAsSprints(User $user, $project)
    {
        Log::info('[Milestones Importer] Starting import process');
        $startTime = time();
        $allProjectMilestonesFromAPI = array();
        $assemblaGateway = new AssemblaGateway($user);
        $sprints = $assemblaGateway->getMilestonesForSpace($project->wikiname);

        $APIEndTime = time();
        $minutes = round(($APIEndTime - $startTime)/60, 2);
        Log::info('[Milestones Importer] API response time '.$minutes.' minutes');

        if ($sprints !== false) {
            /** @var SprintDTO $sprintDto */
            foreach ($sprints as $sprintDto) {
                $allProjectMilestonesFromAPI[$sprintDto->getSprintAssemblaId()] = true;
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

            //sync milestones received from API with project->sprints
            foreach ($project->sprints as $sprint) {
                if (!array_key_exists($sprint->sprint_assembla_id, $allProjectMilestonesFromAPI)) {
                    $project->sprints()->detach($sprint->id);
                    Auth::user()->sprints()->detach($sprint->id);
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
