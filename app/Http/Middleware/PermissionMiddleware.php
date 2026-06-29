<?php

namespace App\Http\Middleware;

use App\Services\PermissionChecker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        PermissionChecker::denyUnless($permission);

        return $next($request);
    }
}
