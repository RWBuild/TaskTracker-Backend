<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationNegTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_checking_if_murugo_user_id_is_not_provided()
    {
        $response = $this->json('POST','/api/auth/login',[
            'murugo_user_id' => '',
            'client_id' => 2,
            'client_secret' => '0tRj9236sRsqi0A6ljeOxdjJFg5HP5QNysInyi96',
            'names' => 'Rwihimba fred',
            'email' => 'rwihimba2@gmail.com'
        ]);

        $response
        ->assertStatus(422)
        ->assertSee('errors')
        ->assertSee('murugo_user_id')
        ->assertJson([
            'message' => 'The given data was invalid.'
        ]);
    }

    public function test_checking_if_murugo_user_id_is_invalid()
    {
        $response = $this->json('POST','/api/auth/login',[
            'murugo_user_id' => '1234567890werty',
            'client_id' => 2,
            'client_secret' => '0tRj9236sRsqi0A6ljeOxdjJFg5HP5QNysInyi96',
            'names' => 'Rwihimba fred',
            'email' => 'rwihimba2@gmail.com'
        ]);

        $response
        ->assertStatus(404)
        ->assertExactJson([
            'success' => false,
            'message' => 'User not allowed'
        ]);
    }

    public function test_checking_if_client_id_is_not_provided()
    {
        $response = $this->json('POST','/api/auth/login',[
            'murugo_user_id' => '14e56fb2d117f402f71f',
            'client_id' => '',
            'client_secret' => '0tRj9236sRsqi0A6ljeOxdjJFg5HP5QNysInyi96',
            'names' => 'Rwihimba fred',
            'email' => 'rwihimba2@gmail.com'
        ]);

        $response
        ->assertStatus(422)
        ->assertSee('errors')
        ->assertSee('client_id')
        ->assertJson([
            'message' => 'The given data was invalid.'
        ]);
    }

    public function test_checking_if_client_id_is_invalid()
    { 
        $response = $this->json('POST','/api/auth/login',[
            'murugo_user_id' => '14e56fb2d117f402f71f',
            'client_id' => 3,
            'client_secret' => '0tRj9236sRsqi0A6ljeOxdjJFg5HP5QNysInyi96',
            'names' => 'Rwihimba fred',
            'email' => 'rwihimba2@gmail.com'
        ]);

        $response
        ->assertStatus(404)
        ->assertExactJson([
            'success' => false,
            'message' => 'Client request not identified'
        ]);
    }
    
}
