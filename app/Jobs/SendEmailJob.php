<?php

namespace App\Jobs;

use App\Mail\MyEmail;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Mail;


class SendEmailJob
{
    protected $toEmail;
    protected $ccEmails;
    protected $template;
    protected $data;
    protected $toName;
    protected $subject;
    protected $fromName;
    protected $fromEmail;
    protected $attachment;
    protected $mime;
    


    public function __construct($template, $data, $toEmail, $toName, $subject, $ccEmails=[], $fromName=null, $fromEmail=null, $attachment=null, $mime=null)
    {
        $this->toEmail = $toEmail;
        $this->ccEmails = $ccEmails;
        $this->template = $template;
        $this->data = $data ;
        $this->toName = $toName;
        $this->subject = $subject; 
        $this->fromName = $fromName;
        $this->fromEmail = $fromEmail;
        $this->attachment = $attachment;
        $this->mime = $mime;

    }

    public function handle()
    {
      
        $toEmail = $this->toEmail;
        $ccEmails = $this->ccEmails;
       
        $toName = $this->toName;
        $subject = $this->subject;
        $data = $this->data;
        $fromName = $this->fromName;
        $fromEmail = $this->fromEmail;
        $attachment = $this->attachment;
        $mime = $this->mime;


        $email_response = Mail::send($this->template, $this->data, function ($message) use($toEmail, $toName, $subject, $data, $fromName, $fromEmail, $ccEmails, $attachment , $mime) {
            $message->to($toEmail, $toName);
            $message->subject($subject);

            if ($fromEmail != '' && $fromName != '') {
                $message->from($fromEmail,  $fromName);
            }

            if (!empty($ccEmails)) {
                $message->cc($ccEmails);
            }

            if($attachment != ''){
                $message->attach($attachment,['as' => 'Result', 'mime' => $mime]);
            }
        });
    

    }
}
