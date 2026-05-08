<?php

namespace App\Mail;

use App\Models\TenantInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $timeout = 60;
    public int $tries   = 2;

    public function __construct(public TenantInvitation $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Você foi convidado para {$this->invitation->tenant->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tenant.invitation',
            with: [
                'tenantName' => $this->invitation->tenant->name,
                'role'       => $this->invitation->role,
                'acceptUrl'  => route('invitations.accept', $this->invitation->token),
                'expiresAt'  => $this->invitation->expires_at->format('d/m/Y H:i'),
            ],
        );
    }
}
