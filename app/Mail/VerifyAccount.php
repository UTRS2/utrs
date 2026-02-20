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

class VerifyAccount extends Mailable
{
    use Queueable, SerializesModels, UsesMailcoachMail;

    /**
     * Create a new message instance.
     */
    public function __construct($email, $url)
    {
        $this->email = $email;
        $this->url = $url;
    }

    public function build()
    {
        $this
        ->mailcoachMail('Appeal pending', ['email' => $this->email, 'verify-url' => $this->url]);
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
