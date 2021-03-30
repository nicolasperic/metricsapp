<?php
/**
 * The only responsibility of this class is to create and update an Entity from a DTO
 */
namespace App\Dto\Mapper;

use App\Dto\ProjectDto;
use App\Project;
use App\SprintIteration;

class ProjectMapper extends AbstractMapper
{
    /**
     *
     * @param ProjectDto $projectDto
     *
     * @return mixed
     */
    public static function createProjectFromDTO(ProjectDto $projectDto)
    {
         $project = Project::create([
            'name' => $projectDto->getName(),
            'code' => $projectDto->getPrefix(),
            'wikiname' => $projectDto->getWikiName(),
            'project_assembla_id' => $projectDto->getProjectAssemblaId(),
            'status' => $projectDto->getStatus(),
        ]);

        $sprintIteration = SprintIteration::createForProject($project);

        return $project;

    }

    /**
     * @param \App\Project $project
     * @param ProjectDto   $projectDto
     *
     * @return Project
     */
    public static function updateProjectFromDTO($project, $projectDto)
    {
        $changed = false;
        if ($projectDto->getName() !== $project->name) {
            $project->name = $projectDto->getName();
            $changed = true;
        }

        if ($projectDto->getStatus() !== $project->status) {
            $project->status = $projectDto->getStatus();
            $changed = true;
        }

        if ($projectDto->getWikiName() !== $project->wiki_name) {
            $project->wikiname = $projectDto->getWikiName();
            $changed = true;
        }

        if ($projectDto->getPrefix() !== $project->code) {
            $project->code = $projectDto->getPrefix();
            $changed = true;
        }

        if ($changed) {
            $project->save();
        }

        return $project;
    }

}