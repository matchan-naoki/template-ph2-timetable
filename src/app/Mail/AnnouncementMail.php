<?php

namespace App\Mail;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnnouncementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Announcement $announcement,
        public string $studentName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【授業のお知らせ】' . $this->announcement->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.announcement',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}