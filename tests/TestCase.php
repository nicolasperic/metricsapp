<?php

namespace Tests;

use App\User;
use PHPUnit\Framework\Assert;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    protected $user;
    protected function setUp(): void
    {
        parent::setUp();

        TestResponse::macro('data', function ($key) {
            return $this->original->getData()[$key];
        });
        Collection::macro('assertContains', function($value) {
            Assert::assertTrue($this->contains($value), 'Failed asserting that the collection contained the specified value. '.$value);
        });
        Collection::macro('assertNotContains', function($value) {
            Assert::assertFalse($this->contains($value), 'Failed asserting that the collection did not cotain the specified value.');
        });
    }

    protected function loginWithAssemblaUser()
    {
        $user = User::factory()->create([
            'assembla_key' => config('services.assemblatesting.assembla_key'),
            'assembla_secret' => config('services.assemblatesting.assembla_secret')
        ]);
        $this->be($user);
        $this->user = $user;

        return $user;
    }
}
