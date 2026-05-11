<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\User;
use App\Services\TenantContext;
use App\Services\TenantMembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly TenantMembershipService $membershipService,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $tenant = $this->context->get();

        $members = $tenant->users()
            ->withPivot(['role', 'is_default', 'status', 'joined_at'])
            ->wherePivot('status', 'active')
            ->orderByPivot('joined_at', 'asc')
            ->get()
            ->each(function (User $member) use ($tenant): void {
                $this->context->set($tenant);

                $member->unsetRelation('roles');
                $member->unsetRelation('permissions');

                $member->setAttribute('tenant_role', $member->getRoleNames()->first());
            });

        if ($request->expectsJson()) {
            return response()->json([
                'data' => MemberResource::collection($members),
            ]);
        }

        return view('members.index', compact('members', 'tenant'));
    }

    public function update(UpdateMemberRequest $request, User $user): JsonResponse|RedirectResponse
    {
        $tenant = $this->context->get();
        $requester = $request->user();

        $this->ensureOwner($requester, $request);

        if ($requester->id === $user->id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Você não pode alterar seu próprio papel.',
                ], 422);
            }

            return back()->with('error', 'Você não pode alterar seu próprio papel.');
        }

        if (!$user->belongsToTenant($tenant->id)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Usuário não pertence a este workspace.',
                ], 404);
            }

            return back()->with('error', 'Usuário não pertence a este workspace.');
        }

        $this->context->set($tenant);

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        $targetRole = $user->getRoleNames()->first();

        if ($targetRole === 'owner') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Não é possível alterar o papel do proprietário.',
                ], 422);
            }

            return back()->with('error', 'Não é possível alterar o papel do proprietário.');
        }

        $this->membershipService->syncUserRoleInTenant(
            user: $user,
            tenant: $tenant,
            role: $request->role,
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Papel atualizado com sucesso.',
            ]);
        }

        return back()->with('success', 'Papel do membro atualizado.');
    }

    public function destroy(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $tenant = $this->context->get();
        $requester = $request->user();

        $this->ensureOwner($requester, $request);

        if ($requester->id === $user->id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Você não pode remover a si mesmo.',
                ], 422);
            }

            return back()->with('error', 'Você não pode remover a si mesmo.');
        }

        if (!$user->belongsToTenant($tenant->id)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Usuário não pertence a este workspace.',
                ], 404);
            }

            return back()->with('error', 'Usuário não pertence a este workspace.');
        }

        $this->context->set($tenant);

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        $targetRole = $user->getRoleNames()->first();

        if ($targetRole === 'owner') {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Não é possível remover o proprietário do workspace.',
                ], 422);
            }

            return back()->with('error', 'Não é possível remover o proprietário.');
        }

        $this->membershipService->detachUserFromTenant(
            user: $user,
            tenant: $tenant,
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Membro removido com sucesso.',
            ]);
        }

        return back()->with('success', 'Membro removido do workspace.');
    }

    private function ensureOwner(User $user, Request $request): void
    {
        if ($user->hasRole('owner')) {
            return;
        }

        if ($request->expectsJson()) {
            abort(response()->json([
                'message' => 'Apenas o proprietário pode gerenciar membros.',
            ], 403));
        }

        abort(403, 'Apenas o proprietário pode gerenciar membros.');
    }
}
