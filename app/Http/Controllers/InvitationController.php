<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteRequest;
use App\Mail\TenantInvitationMail;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function __construct(private TenantContext $context) {}

    /**
     * Envia convite para um e-mail.
     * Apenas owner pode convidar.
     */
    public function invite(InviteRequest $request): JsonResponse|RedirectResponse
    {
        $tenant = $this->context->get();
        $user   = $request->user();

        // Somente owner pode convidar
        $isOwner = $user->tenants()
            ->wherePivot('tenant_id', $tenant->id)
            ->wherePivot('role', 'owner')
            ->exists();

        if (!$isOwner) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Apenas o proprietário pode convidar membros.'], 403);
            }
            return back()->with('error', 'Apenas o proprietário pode convidar membros.');
        }

        // Verifica se já é membro
        $alreadyMember = $tenant->users()
            ->where('email', $request->email)
            ->exists();

        if ($alreadyMember) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Este usuário já é membro do workspace.'], 422);
            }
            return back()->with('error', 'Este usuário já é membro do workspace.');
        }

        // Cancela convite pendente anterior para o mesmo e-mail
        TenantInvitation::query()
            ->where('tenant_id', $tenant->id)
            ->where('email', $request->email)
            ->whereNull('accepted_at')
            ->delete();

        $invitation = TenantInvitation::create([
            'tenant_id'  => $tenant->id,
            'email'      => $request->email,
            'role'       => $request->role,
            'token'      => Str::uuid(),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invitation->email)->send(new TenantInvitationMail($invitation));
        // Mail::to($invitation->email)->sendNow(new TenantInvitationMail($invitation));

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Convite enviado com sucesso.'], 201);
        }

        return back()->with('success', 'Convite enviado com sucesso.');
    }

    /**
     * Aceita o convite via token do e-mail.
     * Rota pública — usuário pode não estar autenticado ainda.
     */
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

        // Se não autenticado, redireciona para registro/login com token
        if (!$request->user()) {
            session(['invitation_token' => $token]);

            return redirect()->route('register')->with(
                'info',
                'Crie sua conta para aceitar o convite de ' . $invitation->tenant->name
            );
        }

        $user = $request->user();

        // Verifica se já é membro
        if ($user->belongsToTenant($invitation->tenant_id)) {
            $invitation->update(['accepted_at' => now()]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Você já é membro deste workspace.'], 422);
            }
            return redirect()->route('dashboard')->with('info', 'Você já é membro deste workspace.');
        }

        // Adiciona à pivot
        $user->tenants()->attach($invitation->tenant_id, [
            'role'       => $invitation->role,
            'is_default' => false,
            'status'     => 'active',
            'joined_at'  => now(),
        ]);

        $invitation->update(['accepted_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Convite aceito com sucesso.']);
        }

        return redirect()->route('tenants.index')->with('success', 'Você entrou no workspace ' . $invitation->tenant->name . '!');
    }

    /**
     * Revoga convite pendente.
     * Apenas owner pode revogar.
     */
    public function revoke(Request $request, TenantInvitation $invitation): JsonResponse|RedirectResponse
    {
        $tenant = $this->context->get();
        $user   = $request->user();

        // Garante que o convite pertence ao tenant atual
        if ($invitation->tenant_id !== $tenant->id) {
            abort(403);
        }

        $isOwner = $user->tenants()
            ->wherePivot('tenant_id', $tenant->id)
            ->wherePivot('role', 'owner')
            ->exists();

        if (!$isOwner) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Apenas o proprietário pode revogar convites.'], 403);
            }
            return back()->with('error', 'Apenas o proprietário pode revogar convites.');
        }

        if ($invitation->isAccepted()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Convite já foi aceito e não pode ser revogado.'], 422);
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
