<?php

namespace App\Mail;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnrollmentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Course $course,
        public Enrollment $enrollment
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Enrollment Confirmation - ' . $this->course->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.enrollment-confirmation',
        );
    }
}
