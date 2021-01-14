<?php
/**
 * The only responsibility of this class is to create and update an Entity from a DTO
 */
namespace App\Dto\Mapper;


use App\Dto\SprintDto;
use App\Sprint;

class SprintMapper extends AbstractMapper
{
    public static function createSprintFromDTO(SprintDto $sprintDto)
    {
         $sprint = Sprint::create([
             'name' => $sprintDto->getTitle(),
             'sprint_assembla_id' => $sprintDto->getSprintAssemblaId(),
             'project_assembla_id' => $sprintDto->getProjectAssemblaId(),
             'start_date' => $sprintDto->getStartDate(),
             'end_date' => $sprintDto->getEndDate(),
             'is_active' => $sprintDto->getStatus(),
             'planner_type' => $sprintDto->getPlannerType()
         ]);

        return $sprint;

    }

    /**
     * @param \App\Sprint $sprint
     * @param SprintDto $sprintDto
     */
    public static function updateSprintFromDTO($sprint, $sprintDto)
    {
        $changed = false;
        if ($sprintDto->getTitle() !== $sprint->name) {
            $sprint->name = $sprintDto->getTitle();
            $changed = true;
        }

        if ($sprintDto->getStatus() !== $sprint->is_active) {
            $sprint->is_active = $sprintDto->getStatus();
            $changed = true;
        }

        if ($sprintDto->getPlannerType() !== $sprint->planner_type) {
            $sprint->planner_type = $sprintDto->getPlannerType();
            $changed = true;
        }

        if ($sprintDto->getStartDate() !== $sprint->start_date) {
            $sprint->start_date = $sprintDto->getStartDate();
            $changed = true;
        }

        if ($sprintDto->getEndDate() !== $sprint->end_date) {
            $sprint->end_date = $sprintDto->getEndDate();
            $changed = true;
        }

        if ($changed) {
            $sprint->save();
        }
    }

}