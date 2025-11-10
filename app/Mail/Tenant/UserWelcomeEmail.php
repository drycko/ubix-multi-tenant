<?php
namespace App\Mail\Tenant;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class UserWelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct($user)
    {
      $this->user = $user;
      $this->adminEmail = config('app.admin_email');
      $this->tenant = tenant();
      $this->temp_password = 'password123'; // Ideally, this should be passed as a parameter
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to Our Service')
          ->markdown('emails.tenant.user-welcome-email')
          ->with([
              'user' => $this->user,
              'adminEmail' => $this->adminEmail,
              'temp_password' => $this->temp_password,
              'tenant' => $this->tenant,
          ]);
    }
}