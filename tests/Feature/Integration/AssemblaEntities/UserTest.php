<?php

namespace Tests\Feature\Integration;

use App\Dto\AssemblaUserDto;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group integration
 *        ^any test that will test my integration with another service
 */
class UserTest
    extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_get_authenticated_user()
    {
        $assemblaGateway = new AssemblaGateway($this->loginWithAssemblaUser());

        /** @var AssemblaUserDto $userDto */
        $userDto = $assemblaGateway->getAuthenticatedUser();
        $this->assertEquals('nicoperic', $userDto->getLogin());
        $this->assertEquals('nperic@summasolutions.net', $userDto->getEmail());
    }

    /** @test */
    function can_get_authenticated_user_image()
    {
        $assemblaGateway = new AssemblaGateway($this->loginWithAssemblaUser());
        $authenticatedUserImagePath = $assemblaGateway->getUserImage('cvixt811Gr4PBcacwqjQYw');

        $this->assertEquals('https://s3.amazonaws.com/assembla-avatars/1e7f71fc/cvixt811Gr4PBcacwqjQYw:1571509138', $authenticatedUserImagePath);

    }

    /** @test */
    function can_get_user_by_assembla_id()
    {
        $assemblaGateway = new AssemblaGateway($this->loginWithAssemblaUser());
        /** @var AssemblaUserDto $userDto */
        $userDto= $assemblaGateway->getUser('cvixt811Gr4PBcacwqjQYw');

        $this->assertEquals('nicoperic', $userDto->getLogin());
        $this->assertEquals('nperic@summasolutions.net', $userDto->getEmail());
    }

    /** @test */
    function can_get_space_users()
    {
        $assemblaGateway = new AssemblaGateway($this->loginWithAssemblaUser());
        $spaceUsers = $assemblaGateway->getSpaceUsers('sommiercenter');


        $this->assertTrue(count($spaceUsers) > 0);
    }

}
