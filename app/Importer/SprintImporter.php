<?php

namespace App\Importer;

use App\Dto\Mapper\SprintMapper;
use App\Dto\SprintDto;
use App\Integration\AssemblaGateway;
use App\Sprint;
use App\User;
use Illuminate\Support\Facades\Log;

class SprintImporter
{
    /**
     * @var User
     */
    private $user;

    function __construct(User $user)
    {
        $this->user = $user;
    }


    /**
     *
     */
    public function importProjectMilestonesAsSprints($project)
    {
        $noContent = true;
        Log::info('[Milestones Importer] Starting import process');
        $startTime = time();
        $allProjectMilestonesFromAPI = array();
        $assemblaGateway = new AssemblaGateway($this->user);
        $sprints = $assemblaGateway->getMilestonesForSpace($project->wikiname);

        $APIEndTime = time();
        $minutes = round(($APIEndTime - $startTime)/60, 2);
        Log::info('[Milestones Importer] API response time '.$minutes.' minutes');

        if ($sprints !== false) {
            /** @var SprintDTO $sprintDto */
            foreach ($sprints as $sprintDto) {
                $allProjectMilestonesFromAPI[$sprintDto->getSprintAssemblaId()] = true;
                $sprint = Sprint::getSprintByAssemblaId($sprintDto->getSprintAssemblaId());
                if ($sprint === null) {
                    $sprint = SprintMapper::createSprintFromDTO($sprintDto);
                    $this->_addSprintToProject($sprint, $project);
                } else {

                    $sprint = SprintMapper::updateSprintFromDTO($sprint, $sprintDto);
                }

                if (!$this->user->hasSprint($sprintDto->getSprintAssemblaId())) {
                    $this->_addSprintToUser($sprint);
                }
            }

            //sync milestones received from API with project->sprints
            foreach ($project->sprints as $sprint) {
                if (!array_key_exists($sprint->sprint_assembla_id, $allProjectMilestonesFromAPI)) {
                    $project->sprints()->detach($sprint->id);
                    $this->user->sprints()->detach($sprint->id);
                }
            }
            $noContent = false;
        }

        return $noContent;
    }

    private function _addSprintToUser($sprint)
    {
        $this->user->sprints()->save($sprint);
    }

    private function _addSprintToProject($sprint, $project)
    {
        $project->sprints()->save($sprint);
    }

    public function createNewCurrentSprint($sprintData, $project)
    {
        //TODO will this be used?...
    }

}
