<?php

namespace App\Integration;

use App\Dto\AssemblaUserDto;
use App\Dto\SprintDto;
use App\User;
use Illuminate\Support\Facades\Log;

/**
 * This class is used to replace the AssemblaGateway instance when testing
 * Class FakeAssemblaGateway
 *
 * @package App\Integration
 */
class FakeAssemblaGateway
{
    /** relationship is 5 - ticket2 is story and ticket1 is subtask of the story */
    const STORY_RELATION = 5;
    /**
     * @var User
     */
    private $user;

    function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Faking the getAuthenticatedUser to pass the tests
     */
    public function getAuthenticatedUser()
    {
        $userData = [
            'id' => 'cvixt811Gr4PBcacwqjQYw',
            'login' => 'nicoperic',
            'name' => 'Nicolás Peric',
            'email' => 'nperic@example.com'

        ];

        return new AssemblaUserDto($userData);
    }

    public function createMilestone($postParams, $space)
    {
        log::info('createMilestone FakeAssembla for '.$space);
        log::info(print_r($postParams, 1));
        $milestoneData = [
            'title' => 'SE - 22MAR21',
        'is_completed' => false,
        'start_date' => '22/03/2021',
        'due_date' => '04/04/2021',
        'id' => 'TESTID1234',
        'space_id' => 'dpT43eCVCr54kBacwqjQYw',
        'planner_type' => \App\Sprint::PLANNER_TYPE_CURRENT
        ];
        $milestone = new SprintDto($milestoneData);

        return $milestone;
    }

    public function updateTicket($putParams, $ticketNumber, $space) {
        log::info('update Ticket '.$ticketNumber.' space '.$space.' '.print_r($putParams, 1));
        return true;
    }

    public function updateMilestone($putParams, $milestoneId, $space)
    {
        log::info('update Milestone '.$milestoneId.' space '.$space.' '.print_r($putParams, 1));
        return true;
    }
}
