<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class NewTenantWelcomeEmail extends Mailable
{
  use Queueable, SerializesModels;
  
  public $tenant;
  public $adminEmail;
  
  public function __construct($tenant)
  {
    $this->tenant = $tenant;
    $this->adminEmail = config('app.admin_email');
    $this->tempPassword = 'password@123'; // Default temporary password from the tenant seeder
  }
  
  public function build()
  {
    return $this->subject('Welcome to Our Service')
    ->markdown('emails.central.new-tenant-welcome')
    ->with([
      'tenant' => $this->tenant,
      'adminEmail' => $this->adminEmail,
      'tempPassword' => $this->tempPassword,
    ]);
  }
}
