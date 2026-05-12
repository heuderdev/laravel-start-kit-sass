<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteRequest;
use App\Services\TenantContext;
use App\Services\TenantInvitationService;
use App\Models\TenantInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly TenantInvitationService $invitationService,
    ) {}

    public function invite(InviteRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $this->invitationService->sendInvitation(
                tenant: $this->context->get(),
                inviter: $request->user(),
                email: $request->email,
                role: $request->role,
            );
        } catch (\DomainException $e) {
            $status = $e->getCode() ?: 422;

            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], $status);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Convite enviado com sucesso.'], 201);
        }

        return back()->with('success', 'Convite enviado com sucesso.');
    }

    public function accept(Request $request, string $token): JsonResponse|RedirectResponse
    {
        if (!$request->user()) {
            $pending = $this->invitationService->findPendingByToken($token);

            if (!$pending || $pending->isExpired()) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Convite inválido ou expirado.'], 410);
                }

                return redirect()->route('welcome')->with('error', 'Convite inválido ou expirado.');
            }

            session(['invitation_token' => $token]);

            return redirect()
                ->route('register')
                ->with('info', 'Crie sua conta para aceitar o convite de ' . $pending->tenant->name);
        }

        try {
            $invitation = $this->invitationService->acceptInvitation($token, $request->user());
        } catch (\DomainException $e) {
            $status = $e->getCode() ?: 410;

            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], $status);
            }

            return redirect()->route('welcome')->with('error', $e->getMessage());
        }

        $alreadyMember = $invitation->wasRecentlyCreated === false
            && $request->user()->belongsToTenant($invitation->tenant_id);

        // Diferencia feedback: membro existente (role sincronizado) vs novo membro
        $wasAlreadyMember = $invitation->wasChanged('accepted_at') === false;

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $wasAlreadyMember
                    ? 'Você já era membro deste workspace. Seu papel foi sincronizado com o convite.'
                    : 'Convite aceito com sucesso.',
            ]);
        }

        return redirect()
            ->route('tenants.index')
            ->with(
                $wasAlreadyMember ? 'info' : 'success',
                $wasAlreadyMember
                    ? 'Você já era membro deste workspace. Seu papel foi atualizado.'
                    : 'Você entrou no workspace ' . $invitation->tenant->name . '!',
            );
    }

    public function revoke(Request $request, TenantInvitation $invitation): JsonResponse|RedirectResponse
    {
        try {
            $this->invitationService->revokeInvitation(
                invitation: $invitation,
                tenant: $this->context->get(),
                revoker: $request->user(),
            );
        } catch (\DomainException $e) {
            $status = $e->getCode() ?: 422;

            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], $status);
            }

            if ($status === 403) {
                abort(403, $e->getMessage());
            }

            return back()->with('error', $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Convite revogado com sucesso.']);
        }

        return back()->with('success', 'Convite revogado.');
    }
}
