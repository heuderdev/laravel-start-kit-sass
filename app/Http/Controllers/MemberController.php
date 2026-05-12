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
        // $this->authorize('manageMembers', $tenant);

        $members = $tenant->activeNonOwnerUsers()
            ->get()
            ->each(function (User $member) use ($tenant): void {
                $member->setAttribute('tenant_role', $member->roleInTenant($tenant));
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
        $tenant    = $this->context->get();
        $requester = $request->user();

        $this->ensureOwner($requester, $request);

        if ($requester->id === $user->id) {
            return $this->respondError($request, 'Você não pode alterar seu próprio papel.', 422);
        }

        if (!$user->belongsToTenant($tenant->id)) {
            return $this->respondError($request, 'Usuário não pertence a este workspace.', 404);
        }

        if ($user->hasRoleInTenant('owner', $tenant)) {
            return $this->respondError($request, 'Não é possível alterar o papel do proprietário.', 422);
        }

        $this->membershipService->syncUserRoleInTenant(
            user: $user,
            tenant: $tenant,
            role: $request->role,
        );

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Papel atualizado com sucesso.']);
        }

        return back()->with('success', 'Papel do membro atualizado.');
    }

    public function destroy(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $tenant    = $this->context->get();
        $requester = $request->user();

        $this->ensureOwner($requester, $request);

        if ($requester->id === $user->id) {
            return $this->respondError($request, 'Você não pode remover a si mesmo.', 422);
        }

        if (!$user->belongsToTenant($tenant->id)) {
            return $this->respondError($request, 'Usuário não pertence a este workspace.', 404);
        }

        if ($user->hasRoleInTenant('owner', $tenant)) {
            return $this->respondError($request, 'Não é possível remover o proprietário do workspace.', 422);
        }

        $this->membershipService->detachUserFromTenant(user: $user, tenant: $tenant);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Membro removido com sucesso.']);
        }

        return back()->with('success', 'Membro removido do workspace.');
    }

    private function ensureOwner(User $user, Request $request): void
    {
        if ($user->hasRoleInTenant('owner', $this->context->get())) {
            return;
        }

        if ($request->expectsJson()) {
            abort(response()->json([
                'message' => 'Apenas o proprietário pode gerenciar membros.',
            ], 403));
        }

        abort(403, 'Apenas o proprietário pode gerenciar membros.');
    }

    private function respondError(Request $request, string $message, int $status): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->with('error', $message);
    }
}
