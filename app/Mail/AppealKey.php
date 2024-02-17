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

class AppealKey extends Mailable
{
    use Queueable, SerializesModels, UsesMailcoachMail;

    /**
     * Create a new message instance.
     */
    public function __construct($appealkey, $email)
    {
        $this->subject = 'UTRS Appeal Key Reset';
        $this->appealkey = $appealkey;
        $this->email = $email;
    }

    /*public function build() {
        $this->mailcoachMail('Appeal Key Reset', ['email' => $this->email, 'appealkey' => $this->appealkey, 'message' => $this->message]);
    }*/

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
            view: 'emails.appealkey',
            with: [
                'email' => $this->email,
                'appealkey' => $this->appealkey,
                'stopUrl' => route('email.ban'),
            ],
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
