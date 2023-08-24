<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class HandlerTest extends TestCase
{
    /**
     * Test handles exception
     * @param object exceptionInstance 
     * @param int expectedHttpCode
     * @param array expectedResponseMessage
     * 
     * @dataProvider providerTestHandleException
     * 
     * @return void
     */
    public function test_handle_exception($exceptionInstance, $expectedHttpCode, $expectedResponseMessage) 
    {
        $handler = app(Handler::class);
        $request = $this->createMock(Request::class);
        $response = $handler->render($request, $exceptionInstance);

        $this->assertEquals($expectedHttpCode, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedResponseMessage), $response->getContent());
        
    }

    public function providerTestHandleException()
    {
        return [
            [new AuthenticationException(), Response::HTTP_UNAUTHORIZED, ['message' => 'Unauthenticated']],
            [new NotFoundHttpException(), Response::HTTP_NOT_FOUND, ['message' => 'Not Found']],
            [new AccessDeniedHttpException(), Response::HTTP_FORBIDDEN, ['message' => 'Permission denied']],
            [new \Exception('Generic exception message'), Response::HTTP_INTERNAL_SERVER_ERROR, ['message' => 'Generic exception message']]
        ];
    }
}
