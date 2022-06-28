<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject, $body, $from;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($from,  $subject, $body)
    {
        //
        $this->from = $from;
        $this->subject = $subject;
        $this->body = $body;
        
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject( $this->subject )
                    ->from ( $this->from )
                    ->view('emails.generic-template');
    }
}
