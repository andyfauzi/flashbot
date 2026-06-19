<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\TenantPayment;
use App\Models\Tenant;

class PaymentSuccessMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $payment;
    public $tenant;

    /**
     * Create a new message instance.
     */
    public function __construct(TenantPayment $payment, Tenant $tenant)
    {
        $this->payment = $payment;
        $this->tenant = $tenant;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Konfirmasi Pembayaran Paket ' . ucfirst($this->payment->plan_name) . ' - Tenanta.id',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment_success',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
