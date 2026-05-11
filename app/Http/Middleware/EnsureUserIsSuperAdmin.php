<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSuperAdmin
{
    public function __construct(
        private readonly PermissionRegistrar $permissionRegistrar,
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'type' => 'https://httpstatuses.io/401',
                    'title' => 'Unauthenticated',
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'detail' => 'Usuário não autenticado.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            return redirect()->route('login');
        }

        $currentTeamId = $this->permissionRegistrar->getPermissionsTeamId();

        $this->permissionRegistrar->setPermissionsTeamId(null);

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        $isSuperAdmin = $user->hasRole('super-admin');

        $this->permissionRegistrar->setPermissionsTeamId($currentTeamId);

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        if (!$isSuperAdmin) {
            if ($request->expectsJson()) {
                return response()->json([
                    'type' => 'https://httpstatuses.io/403',
                    'title' => 'Forbidden',
                    'status' => Response::HTTP_FORBIDDEN,
                    'detail' => 'Acesso permitido apenas para super administradores.',
                ], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, 'Acesso permitido apenas para super administradores.');
        }

        return $next($request);
    }
}
