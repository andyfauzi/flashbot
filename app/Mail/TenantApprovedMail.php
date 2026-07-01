<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TenantApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $storeName;
    public $subdomainUrl;
    public $email;
    public $password;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($storeName, $subdomainUrl, $email, $password)
    {
        $this->storeName = $storeName;
        $this->subdomainUrl = $subdomainUrl;
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Pendaftaran Toko Anda Telah Disetujui! - Tenanta.id')
                    ->view('emails.tenant.approved');
    }
}
