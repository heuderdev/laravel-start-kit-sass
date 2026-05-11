<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminUserController extends Controller
{
    public function __construct(
        private readonly PermissionRegistrar $permissionRegistrar,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $users = User::query()
            ->select(['id', 'name', 'email', 'created_at'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");

                    if (is_numeric($search)) {
                        $subQuery->orWhere('id', (int) $search);
                    }
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->through(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'is_super_admin' => $this->userHasGlobalSuperAdminRole($user),
                ];
            })
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json($users);
        }

        return view('admin.users.index', [
            'users' => $users,
            'filters' => [
                'search' => (string) $request->input('search', ''),
            ],
        ]);
    }

    public function promote(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $this->runWithoutTeamContext(function () use ($user): void {
            Role::findOrCreate('super-admin', 'web');

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            if (!$user->hasRole('super-admin')) {
                $user->assignRole('super-admin');
            }

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Usuário promovido para super-admin com sucesso.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $this->getGlobalRoleNames($user),
                ],
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Usuário promovido para super-admin com sucesso.');
    }

    public function revoke(Request $request, User $user): JsonResponse|RedirectResponse
    {
        if ($request->user()->is($user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Você não pode remover o papel de super-admin de si mesmo.',
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', 'Você não pode remover o papel de super-admin de si mesmo.');
        }

        if (
            filled(config('services.super_admin.email'))
            && strcasecmp($user->email, (string) config('services.super_admin.email')) === 0
        ) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Você não pode remover o papel de super-admin deste usuário protegido.',
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', 'Você não pode remover o papel de super-admin deste usuário protegido.');
        }

        $this->runWithoutTeamContext(function () use ($user): void {
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            if ($user->hasRole('super-admin')) {
                $user->removeRole('super-admin');
            }

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Papel de super-admin removido com sucesso.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $this->getGlobalRoleNames($user),
                ],
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Papel de super-admin removido com sucesso.');
    }

    private function userHasGlobalSuperAdminRole(User $user): bool
    {
        return $this->runWithoutTeamContext(function () use ($user): bool {
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            return $user->hasRole('super-admin');
        });
    }

    private function getGlobalRoleNames(User $user): array
    {
        return $this->runWithoutTeamContext(function () use ($user): array {
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            return $user->getRoleNames()->values()->all();
        });
    }

    private function runWithoutTeamContext(callable $callback): mixed
    {
        $currentTeamId = $this->permissionRegistrar->getPermissionsTeamId();

        try {
            $this->permissionRegistrar->setPermissionsTeamId(null);

            return $callback();
        } finally {
            $this->permissionRegistrar->setPermissionsTeamId($currentTeamId);
        }
    }
}
