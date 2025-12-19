<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PdfAttachmentMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $subjectText;
    protected $messageText;
    protected $pdfPath;

    public function __construct($subjectText, $messageText, $pdfPath)
    {
        $this->subjectText = $subjectText;
        $this->messageText = $messageText;
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        return $this->subject($this->subjectText)
            ->view('emails.simple')
            ->with([
                'content' => $this->messageText,
            ])
            ->attach(
                Storage::path($this->pdfPath),
                [
                    'as' => basename($this->pdfPath),
                    'mime' => 'application/pdf',
                ]
            );
    }
}
