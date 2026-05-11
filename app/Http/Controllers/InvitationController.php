<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteRequest;
use App\Mail\TenantInvitationMail;
use App\Models\TenantInvitation;
use App\Services\TenantContext;
use App\Services\TenantMembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly TenantMembershipService $membershipService,
    ) {}

    public function invite(InviteRequest $request): JsonResponse|RedirectResponse
    {
        $tenant = $this->context->get();
        $user   = $request->user();

        if (!$user->hasRoleInTenant('owner', $tenant)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Apenas o proprietário pode convidar membros.',
                ], 403);
            }

            return back()->with('error', 'Apenas o proprietário pode convidar membros.');
        }

        $alreadyMember = $tenant->users()
            ->where('email', $request->email)
            ->exists();

        if ($alreadyMember) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Este usuário já é membro do workspace.',
                ], 422);
            }

            return back()->with('error', 'Este usuário já é membro do workspace.');
        }

        TenantInvitation::query()
            ->where('tenant_id', $tenant->id)
            ->where('email', $request->email)
            ->whereNull('accepted_at')
            ->delete();

        $invitation = TenantInvitation::create([
            'tenant_id'  => $tenant->id,
            'email'      => $request->email,
            'role'       => $request->role,
            'token'      => (string) Str::uuid(),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invitation->email)->send(new TenantInvitationMail($invitation));

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Convite enviado com sucesso.'], 201);
        }

        return back()->with('success', 'Convite enviado com sucesso.');
    }

    public function accept(Request $request, string $token): JsonResponse|RedirectResponse
    {
        $invitation = TenantInvitation::query()
            ->where('token', $token)
            ->whereNull('accepted_at')
            ->first();

        if (!$invitation || $invitation->isExpired()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Convite inválido ou expirado.'], 410);
            }

            return redirect()->route('welcome')->with('error', 'Convite inválido ou expirado.');
        }

        if (!$request->user()) {
            session(['invitation_token' => $token]);

            return redirect()
                ->route('register')
                ->with('info', 'Crie sua conta para aceitar o convite de ' . $invitation->tenant->name);
        }

        $user = $request->user();

        if ($user->belongsToTenant($invitation->tenant_id)) {
            $invitation->update(['accepted_at' => now()]);

            $this->membershipService->syncUserRoleInTenant(
                user: $user,
                tenant: $invitation->tenant,
                role: $invitation->role,
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Você já era membro deste workspace. Seu papel foi sincronizado com o convite.',
                ], 200);
            }

            return redirect()
                ->route('tenants.index')
                ->with('info', 'Você já era membro deste workspace. Seu papel foi atualizado.');
        }

        $this->membershipService->attachUserToTenant(
            user: $user,
            tenant: $invitation->tenant,
            role: $invitation->role,
            isDefault: false,
            status: 'active',
        );

        $invitation->update(['accepted_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Convite aceito com sucesso.']);
        }

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Você entrou no workspace ' . $invitation->tenant->name . '!');
    }

    public function revoke(Request $request, TenantInvitation $invitation): JsonResponse|RedirectResponse
    {
        $tenant = $this->context->get();
        $user   = $request->user();

        if ($invitation->tenant_id !== $tenant->id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Este convite não pertence ao tenant ativo.',
                ], 403);
            }

            abort(403, 'Este convite não pertence ao tenant ativo.');
        }

        if (!$user->hasRoleInTenant('owner', $tenant)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Apenas o proprietário pode revogar convites.',
                ], 403);
            }

            return back()->with('error', 'Apenas o proprietário pode revogar convites.');
        }

        if ($invitation->isAccepted()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Convite já foi aceito e não pode ser revogado.',
                ], 422);
            }

            return back()->with('error', 'Convite já foi aceito.');
        }

        $invitation->deleteOrFail();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Convite revogado com sucesso.']);
        }

        return back()->with('success', 'Convite revogado.');
    }
}
