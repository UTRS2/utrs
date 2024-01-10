<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Spatie\MailcoachMailer\Concerns\UsesMailcoachMail;

class VerifyAdminAccount extends Mailable
{
    use Queueable, SerializesModels, UsesMailcoachMail;

    /**
     * Create a new message instance.
     */
    public function __construct($email, $url, $username)
    {
        $this->email = $email;
        $this->url = $url;
        $this->username = $username;

    }

    public function build()
    {
        $this
        ->mailcoachMail('Verify Admin', ['email' => $this->email, 'verify-url' => $this->url, 'username' => $this->username]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('noreply@utrs.email', 'UTRS Developers'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
