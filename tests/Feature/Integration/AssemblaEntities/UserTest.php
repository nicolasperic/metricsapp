<?php

namespace Tests\Feature\Integration;

use App\Dto\AssemblaUserDto;
use App\Integration\AssemblaGateway;
use App\Integration\AssemblaRequest;
use Tests\TestCase;

/**
 * @group integration
 *        ^any test that will test my integration with another service
 */
class UserTest
    extends TestCase
{

    /** @test */
    function can_get_authenticated_user()
    {
        $assemblaGateway = new AssemblaGateway();

        /** @var AssemblaUserDto $userDto */
        $userDto = $assemblaGateway->getAuthenticatedUser();
        $this->assertEquals('nicoperic', $userDto->getLogin());
        $this->assertEquals('nperic@summasolutions.net', $userDto->getEmail());
        /*
           array:7 [
          "id" => "cvixt811Gr4PBcacwqjQYw"
          "login" => "nicoperic"
          "name" => "Nicolás Peric"
          "picture" => "https://www.assembla.com/v1/users/cvixt811Gr4PBcacwqjQYw/picture"
          "email" => "nperic@summasolutions.net"
          "organization" => ""
          "phone" => ""
]
         */
    }

    /** @test */
    function can_get_authenticated_user_image()
    {
        $assemblaGateway = new AssemblaGateway();
        $authenticatedUserImagePath = $assemblaGateway->getUserImage('cvixt811Gr4PBcacwqjQYw');

        $this->assertEquals('https://s3.amazonaws.com/assembla-avatars/1e7f71fc/cvixt811Gr4PBcacwqjQYw:1571509138', $authenticatedUserImagePath);

    }

    /** @test */
    function can_get_user_by_assembla_id()
    {
        $assemblaGateway = new AssemblaGateway();
        /** @var AssemblaUserDto $userDto */

        //aVzzeMlw0r6RhdaIC_Qgzw ezegomez, dUHuyGkPGr44k-acwqEsg8 Pedro Rigoli, aSD9Sgwzqr6OoBaH8tHBnc ealvian
        $userDto= $assemblaGateway->getUser('blHTwYuger44kaacwqjQYw');
        dd($userDto->getName());
        $userDto= $assemblaGateway->getUser('cvixt811Gr4PBcacwqjQYw');


        $this->assertEquals('nicoperic', $userDto->getLogin());
        $this->assertEquals('nperic@summasolutions.net', $userDto->getEmail());
    }

    /** @test */
    function can_get_space_users()
    {
        $assemblaGateway = new AssemblaGateway();
        $spaceUsers = $assemblaGateway->getSpaceUsers('sommiercenter');


        $this->assertEquas(count($spaceUsers));
    }

}
