<?php

namespace Tests\Feature\Integration;

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
        $response = $assemblaGateway->getAuthenticatedUser();
        $result = json_decode($response->getBody()->getContents(), 1);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('nicoperic', $result['login']);
        $this->assertEquals('nperic@summasolutions.net', $result['email']);
        /*
         * TODO create assembla_user table with assembla_id, login, name, picture, email
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
        $response = AssemblaRequest::get('users/cvixt811Gr4PBcacwqjQYw/picture');

        $authenticatedUserImagePath = $response->getHeaderLine('X-Guzzle-Redirect-History');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('https://s3.amazonaws.com/assembla-avatars/1e7f71fc/cvixt811Gr4PBcacwqjQYw:1571509138', $authenticatedUserImagePath);

    }

    /** @test */
    function can_get_user_by_assembla_id()
    {
        $assemblaGateway = new AssemblaGateway();
        $response = $assemblaGateway->getUser('cvixt811Gr4PBcacwqjQYw');

        $result = json_decode($response->getBody()->getContents(), 1);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('nicoperic', $result['login']);
        $this->assertEquals('nperic@summasolutions.net', $result['email']);
    }

}
