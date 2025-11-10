<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\DeletePendingBookingsCommand;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: Delete pending bookings every 10 minutes
Schedule::command('bookings:delete-pending')
    ->everyTenMinutes()
    ->withoutOverlapping();

// Schedule: Update overdue invoices daily at 1:00 AM
Schedule::command('invoices:update-overdue')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->onOneServer();

// Schedule: Generate upcoming subscription invoices daily at 2:00 AM (7 days before renewal)
Schedule::command('invoices:generate-upcoming --days=7')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();
