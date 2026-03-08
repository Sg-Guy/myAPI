<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next , string $role): Response
    {
        //dd($role);
        if ($request->user() && $request->user()->role->nom === $role) {
        return $next($request);
    }

    return response()->json(['message' => 'Accès refusé. Rôle insuffisant.'], 403);

    }
}
