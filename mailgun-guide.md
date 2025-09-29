Great! Here’s a step-by-step guide to set up Mailgun with Laravel (works with Sail too):

---

### 1. Install Mailgun SDK (if needed)
Laravel supports Mailgun out of the box, but for API features you can run:
```bash
composer require mailgun/mailgun-php
```

---

### 2. Configure .env file

Add or update these lines in your .env:
```
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-sandbox-or-verified-domain
MAILGUN_SECRET=your-mailgun-api-key
MAILGUN_ENDPOINT=api.mailgun.net
MAIL_FROM_ADDRESS=your@email.com
MAIL_FROM_NAME="${APP_NAME}"
```
Replace with your Mailgun credentials.

---

### 3. Update mail.php

Set the mailer to `mailgun`:
```php
'mailers' => [
    // ...existing mailers...
    'mailgun' => [
        'transport' => 'mailgun',
    ],
],
```

---

### 4. Send a test email

Create a mailable:
```bash
php artisan make:mail TestMail
```

Edit `app/Mail/TestMail.php`:
```php
public function build()
{
    return $this->subject('Test Mailgun')
        ->view('emails.test');
}
```

Create the view `resources/views/emails/test.blade.php`:
```blade
<h1>This is a test email from Mailgun!</h1>
```

Send the email from a controller or tinker:
```php
use App\Mail\TestMail;
use Illuminate\Support\Facades\Mail;

Mail::to('verified@email.com')->send(new TestMail());
```

---

### 5. Troubleshooting

- If using the sandbox domain, only send to verified recipients.
- Check Mailgun logs for delivery status.

---

Let me know if you want a sample controller action for sending a test email!