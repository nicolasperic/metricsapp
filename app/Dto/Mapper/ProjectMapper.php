<?php
/**
 * The only responsibility of this class is to generate an Entity from a DTO
 */
namespace App\Dto\Mapper;

use App\Dto\ProjectDto;
use App\Project;
use Illuminate\Support\Facades\Auth;

class ProjectMapper extends AbstractMapper
{
    public static function createProjectFromDTO(ProjectDto $projectDto)
    {
         $project = Project::create([
            'name' => $projectDto->getName(),
            'code' => $projectDto->getPrefix(),
            'wikiname' => $projectDto->getWikiName(),
            'project_assembla_id' => $projectDto->getProjectAssemblaId(),
            'status' => $projectDto->getStatus(),
        ]);

        return $project;

    }

}