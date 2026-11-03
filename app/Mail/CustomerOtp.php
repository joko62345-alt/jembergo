<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerOtp extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $customerName,
        public bool $isEmailVerification = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->isEmailVerification
                ? 'Verifikasi email JemberGo'
                : 'Kode OTP reset password JemberGo',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-otp',
            with: [
                'otp' => $this->otp,
                'customerName' => $this->customerName,
                'isEmailVerification' => $this->isEmailVerification,
            ],
        );
    }
}
