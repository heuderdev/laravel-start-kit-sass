<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use DomainException;

class ManageTenantMemberService
{
    public function __construct(
        private readonly TenantMembershipService $membershipService,
    ) {}

    /**
     * @throws DomainException
     */
    public function updateRole(
        User $requester,
        User $targetUser,
        Tenant $tenant,
        string $role,
    ): void {
        $this->guardManageMember($requester, $targetUser, $tenant, 'update');

        $this->membershipService->syncUserRoleInTenant(
            user: $targetUser,
            tenant: $tenant,
            role: $role,
        );
    }

    /**
     * @throws DomainException
     */
    public function removeMember(
        User $requester,
        User $targetUser,
        Tenant $tenant,
    ): void {
        $this->guardManageMember($requester, $targetUser, $tenant, 'remove');

        $this->membershipService->detachUserFromTenant(
            user: $targetUser,
            tenant: $tenant,
        );
    }

    /**
     * @throws DomainException
     */
    private function guardManageMember(
        User $requester,
        User $targetUser,
        Tenant $tenant,
        string $action,
    ): void {
        if (!$requester->hasRoleInTenant('owner', $tenant)) {
            throw new DomainException('Apenas o proprietário pode gerenciar membros.', 403);
        }

        if ($requester->id === $targetUser->id) {
            $message = $action === 'update'
                ? 'Você não pode alterar seu próprio papel.'
                : 'Você não pode remover a si mesmo.';

            throw new DomainException($message, 422);
        }

        if (!$targetUser->belongsToTenant($tenant->id)) {
            throw new DomainException('Usuário não pertence a este workspace.', 404);
        }

        if ($targetUser->hasRoleInTenant('owner', $tenant)) {
            $message = $action === 'update'
                ? 'Não é possível alterar o papel do proprietário.'
                : 'Não é possível remover o proprietário do workspace.';

            throw new DomainException($message, 422);
        }
    }
}
