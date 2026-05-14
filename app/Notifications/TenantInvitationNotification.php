<?php

namespace App\Notifications;

use App\Models\TenantInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60;
    public int $tries   = 2;

    public function __construct(public TenantInvitation $invitation) {}

    public function via(object $notifiable): array
    {
        // dispara email E salva no banco (sininho)
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Você foi convidado para {$this->invitation->tenant->name}")
            ->markdown('emails.tenant.invitation', [
                'tenantName' => $this->invitation->tenant->name,
                'role'       => $this->invitation->role,
                'acceptUrl'  => route('invitations.accept', $this->invitation->token),
                'expiresAt'  => $this->invitation->expires_at->format('d/m/Y H:i'),
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message'    => "Você foi convidado para {$this->invitation->tenant->name}",
            'tenant_id'  => $this->invitation->tenant_id,
            'role'       => $this->invitation->role,
            'action_url' => route('invitations.accept', $this->invitation->token),
            'expires_at' => $this->invitation->expires_at->toIso8601String(),
        ];
    }
}
