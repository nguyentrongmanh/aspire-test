<?php

namespace Tests\Unit\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class AuthenticateTest extends TestCase
{
    public function test_authentication()
    {
        $response = $this->get(route('loans.index'));
        $response->assertJson(['message' => 'Unauthenticated']);
    }
}
