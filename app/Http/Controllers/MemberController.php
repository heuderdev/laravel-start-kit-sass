<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function __construct(private TenantContext $context) {}

    public function index(Request $request): View|JsonResponse
    {
        $tenant  = $this->context->get();

        $members = $tenant->users()
            ->withPivot(['role', 'is_default', 'status', 'joined_at'])
            ->wherePivot('status', 'active')
            ->orderByPivot('joined_at', 'asc')
            ->get();

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

        $this->ensureOwner($requester, $tenant->id, $request);

        // Não pode alterar o próprio role
        if ($requester->id === $user->id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Você não pode alterar seu próprio papel.'], 422);
            }
            return back()->with('error', 'Você não pode alterar seu próprio papel.');
        }

        // Garante que o usuário é membro do tenant
        if (!$user->belongsToTenant($tenant->id)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Usuário não pertence a este workspace.'], 404);
            }
            return back()->with('error', 'Usuário não pertence a este workspace.');
        }

        // Não pode rebaixar outro owner
        $targetRole = $user->tenants()
            ->wherePivot('tenant_id', $tenant->id)
            ->first()?->pivot->role;

        if ($targetRole === 'owner') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Não é possível alterar o papel do proprietário.'], 422);
            }
            return back()->with('error', 'Não é possível alterar o papel do proprietário.');
        }

        $requester->tenants()->updateExistingPivot($user->id, [
            'role' => $request->role,
        ]);

        // Corrigido: atualiza a pivot do user alvo, não do requester
        $user->tenants()->updateExistingPivot($tenant->id, [
            'role' => $request->role,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Papel atualizado com sucesso.']);
        }

        return back()->with('success', 'Papel do membro atualizado.');
    }

    public function destroy(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $tenant    = $this->context->get();
        $requester = $request->user();

        $this->ensureOwner($requester, $tenant->id, $request);

        // Não pode remover a si mesmo
        if ($requester->id === $user->id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Você não pode remover a si mesmo.'], 422);
            }
            return back()->with('error', 'Você não pode remover a si mesmo.');
        }

        // Garante que o usuário é membro do tenant
        if (!$user->belongsToTenant($tenant->id)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Usuário não pertence a este workspace.'], 404);
            }
            return back()->with('error', 'Usuário não pertence a este workspace.');
        }

        // Não pode remover outro owner
        $targetRole = $user->tenants()
            ->wherePivot('tenant_id', $tenant->id)
            ->first()?->pivot->role;

        if ($targetRole === 'owner') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Não é possível remover o proprietário do workspace.'], 422);
            }
            return back()->with('error', 'Não é possível remover o proprietário.');
        }

        $user->tenants()->detach($tenant->id);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Membro removido com sucesso.']);
        }

        return back()->with('success', 'Membro removido do workspace.');
    }

    // -------------------------------------------------------------------------

    private function ensureOwner(User $user, int $tenantId, Request $request): void
    {
        $isOwner = $user->tenants()
            ->wherePivot('tenant_id', $tenantId)
            ->wherePivot('role', 'owner')
            ->exists();

        if (!$isOwner) {
            if ($request->expectsJson()) {
                abort(response()->json(['message' => 'Apenas o proprietário pode gerenciar membros.'], 403));
            }
            abort(403, 'Apenas o proprietário pode gerenciar membros.');
        }
    }
}
