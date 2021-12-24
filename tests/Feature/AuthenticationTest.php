<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseMigrations;

    private $user = 'test-thiego@email.com';

    private $password = '1@2@3@4@5@6-batata';

    public function setUp(): void
    {
        parent::setUp();

        $user = new User([
            'email'    => $this->user,
            'password' => $this->password,
        ]);

        $user->save();
    }

//    /** @test */
//    public function it_will_register_a_user()
//    {
//        $response = $this->post('/register', [
//            'email'    =>  $this->user,
//            'password' => '123456'
//        ]);
//
//        $response->assertJsonStructure([
//            'access_token',
//            'token_type',
//            'expires_in'
//        ]);
//    }

    /** @test */
    public function it_will_log_a_user_in()
    {
        $response = $this->post('/login', [
            'email'    =>  $this->user,
            'password' =>  $this->password,
        ]);

        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in'
        ]);
    }

    /** @test */
    public function it_will_not_log_an_invalid_user_in()
    {
        $response = $this->post('/login', [
            'email'    => $this->user,
            'password' => 'notlegitpassword'
        ]);

        $response->assertJsonStructure([
            'error',
        ]);
    }
}
