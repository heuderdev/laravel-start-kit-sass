<?php

namespace App\Services;

use App\Mail\TenantInvitationMail;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use App\Notifications\TenantInvitationNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class TenantInvitationService
{
    public function __construct(
        private readonly TenantMembershipService $membershipService,
    ) {}

    /**
     * Cria e envia um convite para o tenant.
     *
     * @throws \DomainException
     */
    public function sendInvitation(Tenant $tenant, User $inviter, string $email, string $role): TenantInvitation
    {
        if (!$inviter->hasRoleInTenant('owner', $tenant)) {
            throw new \DomainException('Apenas o proprietário pode convidar membros.');
        }

        $alreadyMember = $tenant->users()
            ->where('email', $email)
            ->exists();

        if ($alreadyMember) {
            throw new \DomainException('Este usuário já é membro do workspace.');
        }

        // Remove convites pendentes anteriores para o mesmo e-mail
        TenantInvitation::query()
            ->where('tenant_id', $tenant->id)
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->delete();

        $invitation = TenantInvitation::create([
            'tenant_id'  => $tenant->id,
            'email'      => $email,
            'role'       => $role,
            'token'      => (string) Str::uuid(),
            'expires_at' => now()->addDays(7),
        ]);

        // Mail::to($invitation->email)->send(new TenantInvitationMail($invitation));

        $user = User::query()->where('email', $invitation->email)->first();

        if ($user) {
            $user->notify(new TenantInvitationNotification($invitation));
        } else {
            Notification::route('mail', $invitation->email)
                ->notify(new TenantInvitationNotification($invitation));
        }

        return $invitation;
    }

    /**
     * Aceita um convite pelo token.
     * Retorna o convite aceito ou null se inválido/expirado.
     *
     * @throws \DomainException
     */
    public function acceptInvitation(string $token, User $user): TenantInvitation
    {
        $invitation = TenantInvitation::query()
            ->where('token', $token)
            ->whereNull('accepted_at')
            ->first();

        if (!$invitation || $invitation->isExpired()) {
            throw new \DomainException('Convite inválido ou expirado.', 410);
        }

        if ($user->belongsToTenant($invitation->tenant_id)) {
            $invitation->update(['accepted_at' => now()]);

            $this->membershipService->syncUserRoleInTenant(
                user: $user,
                tenant: $invitation->tenant,
                role: $invitation->role,
            );

            return $invitation;
        }

        $this->membershipService->attachUserToTenant(
            user: $user,
            tenant: $invitation->tenant,
            role: $invitation->role,
            isDefault: false,
            status: 'active',
        );

        $invitation->update(['accepted_at' => now()]);

        return $invitation;
    }

    /**
     * Busca um convite por token sem aceitar (para verificação antes do login).
     */
    public function findPendingByToken(string $token): ?TenantInvitation
    {
        return TenantInvitation::query()
            ->where('token', $token)
            ->whereNull('accepted_at')
            ->first();
    }

    /**
     * Revoga (deleta) um convite pendente.
     *
     * @throws \DomainException
     */
    public function revokeInvitation(TenantInvitation $invitation, Tenant $tenant, User $revoker): void
    {
        if ($invitation->tenant_id !== $tenant->id) {
            throw new \DomainException('Este convite não pertence ao tenant ativo.', 403);
        }

        if (!$revoker->hasRoleInTenant('owner', $tenant)) {
            throw new \DomainException('Apenas o proprietário pode revogar convites.');
        }

        if ($invitation->isAccepted()) {
            throw new \DomainException('Convite já foi aceito e não pode ser revogado.', 422);
        }

        $invitation->deleteOrFail();
    }
}
