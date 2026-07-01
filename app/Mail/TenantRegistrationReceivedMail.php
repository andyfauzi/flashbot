<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\TenantRequest;

class TenantRegistrationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tenantRequest;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(TenantRequest $tenantRequest)
    {
        $this->tenantRequest = $tenantRequest;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Pendaftaran Toko Anda Sedang Diproses - Tenanta.id')
                    ->view('emails.tenant.registration_received');
    }
}
