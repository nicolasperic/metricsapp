<?php

namespace App\Importer;

use App\AssemblaUser;
use App\Dto\AssemblaUserDto;
use App\Dto\Mapper\AssemblaUserMapper;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use App\Project;
use App\User;
use Illuminate\Support\Facades\Log;

class UserImporter
{
    private $assemblaGateway;
    function __construct(User $user)
    {
        $this->assemblaGateway = new AssemblaGateway($user);
    }

    /**
     * @param $project Project
     *
     */
    public function importSpaceUsers($project)
    {
        Log::info('[AssemblaUsers Importer] Started');

        $page = 1;
        $queryParams = [
            'page' => $page,
        ];

        $allSpaceUsersFromAPI = array();
        do {

            $assemblaUsers = $this->assemblaGateway->getSpaceUsers($project->wikiname, $queryParams);

            if ($assemblaUsers) {
                Log::info('[AssemblaUsers Importer] Response 200 for page '.$page);
                $queryParams['page'] = ++$page;

                /** @var AssemblaUserDto $assemblaUserDto */
                foreach ($assemblaUsers as $assemblaUserDto) {
                    $allSpaceUsersFromAPI[$assemblaUserDto->getUserAssemblaId()] = true;

                    if (!AssemblaUser::userExists($assemblaUserDto->getUserAssemblaId())) {
                        Log::info('[AssemblaUsers Importer] about to create user '.$assemblaUserDto->getName());
                        $this->_createUserFromDTO($assemblaUserDto, $project);
                    } else {
                        $assemblaUser = AssemblaUser::getUserByAssemblaId($assemblaUserDto->getUserAssemblaId());
                        if (!$project->assemblaUsers()->find($assemblaUser->id)) {
                            $this->_assignUserToProject($assemblaUser, $project);
                        }
                    }
                }
            } else {
                break;
            }
        } while(count($assemblaUsers) === AssemblaRequest::PER_PAGE);

        foreach ($project->assemblaUsers as $user) {
            if (!array_key_exists($user->user_assembla_id, $allSpaceUsersFromAPI)) {
                $project->assemblaUsers()->detach($user->id);
            }
        }
        Log::info('[AssemblaUsers Importer] Ended');
    }

    private function _createUserFromDTO(AssemblaUserDto $assemblaUserDto, $project)
    {
        $assemblaUser = AssemblaUserMapper::createAssemblaUserFromDTO($assemblaUserDto, $project);
        Log::info("AssemblaUser created {$assemblaUserDto->getName()} {$assemblaUserDto->getUserAssemblaId()} {$assemblaUserDto->getEmail()}");
        Log::info('[AssemblaUser Importer] adding user to project'.$project->name);
        $this->_assignUserToProject($assemblaUser, $project);
    }
    
    private function _assignUserToProject($assemblaUser, $project)
    {
        $project->assemblaUsers()->save($assemblaUser);//adding assembla user to project
    }
}
