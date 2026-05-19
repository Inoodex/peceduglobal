<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $studentName,
        public string $studentEmail,
        public string $password,
        public ?string $consultantName = null,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to PecEduGlobal – Your Account is Ready',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.student-registered',
            with: [
                'studentName'    => $this->studentName,
                'studentEmail'   => $this->studentEmail,
                'password'       => $this->password,
                'consultantName' => $this->consultantName,
                'loginUrl'       => config('app.url') . '/login',
            ],
        );
    }
}
