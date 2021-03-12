<?php

namespace App\Importer;

use App\AssemblaUser;
use App\Dto\AssemblaUserDto;
use App\Dto\Mapper\AssemblaUserMapper;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use App\Project;
use App\User;
use GuzzleHttp\Exception\ClientException;
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

                    $assemblaUser = AssemblaUser::getUserByAssemblaId($assemblaUserDto->getUserAssemblaId());
                    if ($assemblaUser === null) {
                        Log::info('[AssemblaUsers Importer] about to create user '.$assemblaUserDto->getName());
                        $image = $this->assemblaGateway->getUserImage($assemblaUserDto->getUserAssemblaId());
                        Log::info('Image obtained for user '.$image);
                        $assemblaUserDto->setPicture($image);

                        $this->_createUserFromDTO($assemblaUserDto, $project);
                    } else {
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

    /**
     * This function will receive an ID from a user in Assembla
     * retrieve it from the API and store it locally
     *
     * @param $userAssemblaId
     * @return bool | \App\AssemblaUser
     * @throws \Exception if an exception is received from the Assembla Gateway is passed on
     */
    public function importUser($userAssemblaId)
    {
        $user = false;
        try {
            Log::info('[AssemblaUser Importer] Started');
            $assemblaUserDto = $this->assemblaGateway->getUser($userAssemblaId);
            if ($assemblaUserDto !== false) {
                Log::info('[AssemblaUsers Importer] about to create user '.$assemblaUserDto->getName());
                try {
                    $image = $this->assemblaGateway->getUserImage($assemblaUserDto->getUserAssemblaId());
                    Log::info('Image obtained for user '.$image);
                    $assemblaUserDto->setPicture($image);
                } catch (ClientException $e) {
                    $image = 'https://assets3.assembla.com/assets/avatars/small/10-34646632626633326534663337306230663564393237353266396538633232383833626339353837396534323061616337666664633662376434376637303134.png';
                    $assemblaUserDto->setPicture($image);
                    if ($e->getCode() == 401) {
                        Log::info('Not authorized to get user image..');
                    } else {
                        Log::info('Error Code '.$e->getCode() . ' when retrieving user image');
                        Log::info($e->getMessage());
                    }
                } catch (\Exception $e) {
                    Log::info($e->getMessage() . '.. Exception $e');
                }


                $user = AssemblaUserMapper::createAssemblaUserFromDTO($assemblaUserDto);
            } else {
                Log::error('User Assembla ID '.$userAssemblaId. ' not found with API');
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;//rethrowing the logged exception to be correctly handled later
        }

        return $user;

    }

    /**
     * Returns the user assembla ID name
     * if the assembla user is already on the DB we return the stored name
     * if not it will import the user, store it and return the name
     *
     * This function is only used on reports that are executedo on the background.
     * We should never call this function from outside a job (performance degradation)
     * @param $userAssemblaId
     *
     * @return String
     * @throws \Exception
     */
    public function getUserName($userAssemblaId)
    {
        $assemblaUser = AssemblaUser::where('user_assembla_id', $userAssemblaId)->first();
        if ($assemblaUser !== null) {
            $userName = $assemblaUser->name;
        } else {
            $assemblaUser = $this->importUser($userAssemblaId);
            $userName = ($assemblaUser)? $assemblaUser->name : $userAssemblaId;
        }

        return $userName;
    }

}
