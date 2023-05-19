<?php

namespace App\Jobs;

use App\Mail\NotifyMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class NotifyMailJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $a_toRecipients;
    protected $a_ccRecipients;
    protected $a_bccRecipients;
    protected $subject;
    protected $body;
    protected $from;

    protected $uniqid;      // Unqiue ID

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($a_toRecipients, $a_ccRecipients, $a_bccRecipients, $from, $subject, $body)
    {
        //
        $this->a_toRecipients = $a_toRecipients;
        $this->a_ccRecipients = $a_ccRecipients;
        $this->a_bccRecipients = $a_bccRecipients;

        $this->subject = $subject;
        $this->body = $body;
        $this->from = $from;

        // Use for preventing overlapping and unqiue in the queue
        $this->uniqid = uniqid();

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        Mail::to( $this->a_toRecipients )
            ->cc( $this->a_ccRecipients )
            ->bcc( $this->a_bccRecipients )   
            ->send(new NotifyMail( $this->from, $this->subject, $this->body ));

    }


    public function uniqueId()
    {
        return $this->uniqid;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {

        echo "The job (NotifyMailJob) with process history id " . $this->uniqid . " started at " . now() . PHP_EOL;
        // If you don’t want any overlapping jobs to be released back onto the queue, you can use the dontRelease method
        return [(new WithoutOverlapping($this->uniqid))->dontRelease()];
    }

}
