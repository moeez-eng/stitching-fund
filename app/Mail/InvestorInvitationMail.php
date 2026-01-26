<?php

namespace App\Mail;

use App\Models\UserInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvestorInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public UserInvitation $invitation
    ) {}

    public function content(): Content
    {
        return new Content(
            view: 'emails.investor-invitation',
            with: [
                'invitation' => $this->invitation,
                'registerUrl' => route('filament.admin.auth.register', ['invitation' => $this->invitation->unique_code]),
                'logoPath' => public_path('images/logo.png'),
            ]
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation to Join as Investor - ' . $this->invitation->company_name,
        );
    }
}
