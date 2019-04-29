<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Mail\Mailer;

use Mail;
use App\Mail\NotificationEmail;

class SendNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $to;
    public $subject;
    public $message;
    public $replyto;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to, $subject, $message, $replyTo)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;
        $this->replyto = $replyTo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new NotificationEmail($this->subject, $this->message, $this->replyto);
        Mail::to($this->to)->queue($email);
    }
}
