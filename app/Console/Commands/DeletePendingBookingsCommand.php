<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Carbon\Carbon;

class DeletePendingBookingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:delete-pending {minutes=7 : Minutes after which pending bookings are deleted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all pending bookings older than the specified number of minutes.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = (int) $this->argument('minutes');
        $timeLimit = Carbon::now()->subMinutes($minutes);
        $count = Booking::where('status', 'pending')
            ->where('created_at', '<', $timeLimit)
            ->delete();
        $this->info("Deleted $count pending bookings older than $minutes minutes.");
    }
}
