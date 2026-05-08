<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class SuperAdminUserController extends Controller
{
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
            ->with('roles:id,name')
            ->orderByDesc('id')
            ->paginate(20)
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
        Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);

        if (!$user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Usuário promovido para super-admin com sucesso.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->fresh()->getRoleNames()->values(),
                ],
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Usuário promovido para super-admin com sucesso.');
    }

    public function revoke(Request $request, User $user): JsonResponse|RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Você não pode remover o papel de super-admin de si mesmo.',
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', 'Você não pode remover o papel de super-admin de si mesmo.');
        }

        if ($user->email === config('services.super_admin.email')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Você não pode remover o papel de super-admin.',
                ], 422);
            }

            return redirect()
                ->back()
                ->with('error', 'Você não pode remover o papel de super-admin.');
        }

        if ($user->hasRole('super-admin')) {
            $user->removeRole('super-admin');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Papel de super-admin removido com sucesso.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->fresh()->getRoleNames()->values(),
                ],
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Papel de super-admin removido com sucesso.');
    }
}
