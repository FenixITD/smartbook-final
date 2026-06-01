<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class EnsureUserIsAdminTest extends TestCase
{
    public function test_it_aborts_when_user_is_null(): void
    {
        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn () => null);

        $middleware = new EnsureUserIsAdmin();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Access denied.');

        $middleware->handle($request, fn () => new Response());
    }

    public function test_it_aborts_when_user_is_not_admin(): void
    {
        $user = new User();
        $user->role = 'user';

        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn () => $user);

        $middleware = new EnsureUserIsAdmin();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Access denied.');

        $middleware->handle($request, fn () => new Response());
    }

    public function test_it_allows_admin_user(): void
    {
        $user = new User();
        $user->role = 'admin';

        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn () => $user);

        $middleware = new EnsureUserIsAdmin();

        $expectedResponse = new Response('Passed');
        $response = $middleware->handle($request, fn () => $expectedResponse);

        $this->assertSame($expectedResponse, $response);
        $this->assertSame('Passed', $response->getContent());
    }
}
