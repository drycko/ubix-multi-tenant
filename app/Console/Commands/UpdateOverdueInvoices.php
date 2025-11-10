<?php

namespace App\Console\Commands;

use App\Models\SubscriptionInvoice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateOverdueInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:update-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update pending invoices to overdue status when past due date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for overdue invoices...');

        // Get all pending invoices past their due date
        $overdueInvoices = SubscriptionInvoice::where('status', 'pending')
            ->where('due_date', '<', Carbon::today())
            ->get();

        if ($overdueInvoices->isEmpty()) {
            $this->info('No overdue invoices found.');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($overdueInvoices as $invoice) {
            $invoice->markAsOverdue();
            $count++;
            
            $this->line("Invoice #{$invoice->invoice_number} marked as overdue (Tenant: {$invoice->tenant->name})");
        }

        $this->info("Successfully updated {$count} invoice(s) to overdue status.");

        return Command::SUCCESS;
    }
}
