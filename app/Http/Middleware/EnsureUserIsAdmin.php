<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Middleware — это "страж" перед контроллером.
// Если пользователь не администратор — он получит 403 (Forbidden).
class EnsureUserIsAdmin
{
    // handle() вызывается для каждого запроса, который проходит через этот middleware.
    // $next — это следующий обработчик в цепочке (в конце — сам контроллер).
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            abort(403, 'Доступ запрещён.');
        }

        return $next($request);
    }
}
