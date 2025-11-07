<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class TenantCreatedAdminEmail extends Mailable
{
  use Queueable, SerializesModels;
  
  public $tenant;
  public $adminEmail;
  
  public function __construct($tenant)
  {
    $this->tenant = $tenant;
    $this->adminEmail = config('app.admin_email');
  }
  
  public function build()
  {
    return $this->subject('New Tenant Created: ' . $this->tenant->name)
    ->markdown('emails.tenant-created-admin')
    ->with([
      'tenant' => $this->tenant,
      'adminEmail' => $this->adminEmail,
    ]);
  }
}
// give me an email template in markdown for this class