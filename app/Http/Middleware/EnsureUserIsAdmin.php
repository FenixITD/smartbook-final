<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() === null || $request->user()->role !== 'admin') {
            abort(403, 'Access denied.');
        }

        /** @var Response $response */
        $response = $next($request);
        return $response;
    }
}
