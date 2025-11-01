<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MagicLoginMail extends Mailable
{
    use Queueable, SerializesModels;

    public $guest;
    public $link;

    public function __construct($guest, $link)
    {
        $this->guest = $guest;
        $this->link = $link;
    }

    public function build()
    {
        return $this->subject('Your Secure Login Link')
            ->markdown('emails.tenant.magic-login');
    }
}