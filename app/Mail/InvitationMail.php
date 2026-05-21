<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invitation $invitation)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->invitation->email],
            subject: 'You have been invited to URL Shortener SaaS',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation',
            with: [
                'invitation' => $this->invitation,
                'acceptUrl' => route('invitations.accept.show', $this->invitation->token),
                'companyName' => $this->invitation->company?->name,
                'role' => $this->invitation->role,
            ],
        );
    }
}
