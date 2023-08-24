<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\Response;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test login success
     *
     * @return void
     */
    public function test_login_success()
    {
        $response = $this->post(route('login'), [
            'email' => $this->user->email,
            'password' => 'password',
        ]);
        $this->assertAuthenticated();
        $response->assertStatus(Response::HTTP_OK);
    }

    /**
     * Test validate login
     *
     * @return void
     */
    public function test_validate_login()
    {
        $response = $this->post(route('login'), [
            'email' => $this->user->email,
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonStructure([
                'errors' => [
                    'password',
                ],
            ]);
    }

    /**
     * Test user login with invalid password
     *
     * @return void
     */
    public function test_invalid_password()
    {
        $response = $this->post(route('login'), [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJsonStructure([
                'message',
            ]);
    }
}
