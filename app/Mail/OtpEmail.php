<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

class OtpEmail extends Mailable {
    use Queueable, SerializesModels;
    public $otp;

    public function __construct($otp) { $this->otp = $otp; }

    public function content(): Content {
        return new Content(view: 'emails.otp');
    }
}
