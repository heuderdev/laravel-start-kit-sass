<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSuperAdmin
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('super-admin')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'type'   => 'https://httpstatuses.io/403',
                    'title'  => 'Forbidden',
                    'status' => Response::HTTP_FORBIDDEN,
                    'detail' => 'Acesso permitido apenas para super administradores.',
                ], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, 'Acesso permitido apenas para super administradores.');
        }

        return $next($request);
    }
}
