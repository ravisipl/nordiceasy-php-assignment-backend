<?php

namespace App\Jobs;

use App\Mail\MyEmail;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class TestMailJob
{
    protected $recipient;

    public function __construct()
    {
        // $this->recipient = $recipient;
    }

    public function handle()
    {
        //$recipient = 'recipient@example.com';
        Log::info("Hello");
        // Helper::sendEmail('mailtemplates.testing_template', [], 'rishabh.meena@softude.com', 'Rishabh meena',  'Testing email');

    }
}
