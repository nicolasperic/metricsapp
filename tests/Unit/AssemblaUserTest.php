<?php

namespace Tests\Unit;

use App\AssemblaUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssemblaUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function can_retrieve_user_by_assembla_id()
    {
        factory(AssemblaUser::class)->create([
            'user_assembla_id' => 'TESTID1234',
            'login' => 'johndoe',
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
        ]);

        $loadedUser = AssemblaUser::getUserByAssemblaId('TESTID1234');

        $this->assertEquals('TESTID1234', $loadedUser->user_assembla_id);
        $this->assertEquals('johndoe', $loadedUser->login);
        $this->assertEquals('John Doe', $loadedUser->name);
        $this->assertEquals('johndoe@example.com', $loadedUser->email);
    }
}
