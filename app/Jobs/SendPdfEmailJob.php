<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\PdfAttachmentMail;
use Throwable;

class SendPdfEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    protected $to;
    protected $cc;
    protected $subject;
    protected $message;
    protected $pdfPath;

    public function __construct($to, $cc, $subject, $message, $pdfPath)
    {
        $this->to = $to;
        $this->cc = $cc;
        $this->subject = $subject;
        $this->message = $message;
        $this->pdfPath = $pdfPath;
    }

    public function middleware()
    {
        return [new WithoutOverlapping(md5(json_encode($this->to)))];
    }

    public function handle()
    {
        Mail::to($this->to)
            ->cc($this->cc)
            ->send(new PdfAttachmentMail(
                $this->subject,
                $this->message,
                $this->pdfPath
            ));
    }

    public function failed(Throwable $exception)
    {
        Log::error('Email job failed', [
            'error' => $exception->getMessage(),
            'recipients' => $this->to,
        ]);
    }
}
