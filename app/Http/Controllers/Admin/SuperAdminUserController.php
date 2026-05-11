<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperAdminUserController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $users = User::query()
            ->select(['id', 'name', 'email', 'is_super_admin', 'created_at'])
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
            ->through(fn(User $user) => [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'created_at'     => $user->created_at,
                'is_super_admin' => $user->isSuperAdmin(),
            ])
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json($users);
        }

        return view('admin.users.index', [
            'users'   => $users,
            'filters' => [
                'search' => (string) $request->input('search', ''),
            ],
        ]);
    }

    public function promote(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $user->update(['is_super_admin' => true]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Usuário promovido para super-admin com sucesso.',
                'data'    => [
                    'id'             => $user->id,
                    'name'           => $user->name,
                    'email'          => $user->email,
                    'is_super_admin' => true,
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

        $user->update(['is_super_admin' => false]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Papel de super-admin removido com sucesso.',
                'data'    => [
                    'id'             => $user->id,
                    'name'           => $user->name,
                    'email'          => $user->email,
                    'is_super_admin' => false,
                ],
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Papel de super-admin removido com sucesso.');
    }
}
