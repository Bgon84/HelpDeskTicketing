<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $message;
    public $replyto;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $message, $replyTo)
    {
        $this->subject = $subject;
        $this->message = $message;
        $this->replyto = $replyTo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('slicktix@intxlog.com')
                    ->subject($this->subject)
                    ->replyTo($this->replyto)
                    ->view('emails.smtptestemail')
                    ->with(
                        [
                            'title' => 'Notification from your Helpdesk',
                            'body' => $this->message
                        ]);
    }
}
