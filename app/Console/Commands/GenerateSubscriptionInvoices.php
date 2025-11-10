<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\Tax;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateSubscriptionInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-upcoming 
                            {--days=7 : Number of days before subscription end date to generate invoice}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate invoices for upcoming subscription renewals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysAhead = (int) $this->option('days');
        $targetDate = Carbon::today()->addDays($daysAhead);

        $this->info("Checking for subscriptions ending on {$targetDate->format('Y-m-d')}...");

        // Get active subscriptions ending on the target date
        $subscriptions = Subscription::where('status', 'active')
            ->whereDate('end_date', $targetDate->format('Y-m-d'))
            ->whereDoesntHave('invoices', function ($query) use ($targetDate) {
                // Check if invoice for this period already exists
                $query->whereDate('invoice_date', '>=', Carbon::today())
                      ->whereDate('due_date', '>=', $targetDate->subDays(7));
            })
            ->with(['tenant', 'plan'])
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No subscriptions found for invoice generation.');
            return Command::SUCCESS;
        }

        $count = 0;
        $errors = 0;

        foreach ($subscriptions as $subscription) {
            try {
                // Calculate next billing period
                $nextStartDate = $subscription->end_date->addDay();
                $nextEndDate = $this->calculateNextEndDate($subscription, $nextStartDate);

                // Get plan amount
                $planAmount = $this->getPlanAmount($subscription);

                if (!$planAmount) {
                    $this->error("Subscription #{$subscription->id}: Could not determine plan amount");
                    $errors++;
                    continue;
                }

                // Get active tax if any
                $tax = Tax::where('is_active', true)
                    ->orderBy('display_order')
                    ->first();

                // Calculate amounts
                $subtotalAmount = $planAmount;
                $taxAmount = 0;
                $totalAmount = $planAmount;

                if ($tax) {
                    if ($tax->type === 'percentage') {
                        if ($tax->is_inclusive) {
                            // Tax is already included in the price
                            $taxAmount = $tax->calculateInclusiveTaxAmount($planAmount);
                            $subtotalAmount = $planAmount - $taxAmount;
                        } else {
                            // Tax is additional
                            $taxAmount = $tax->calculateTaxAmount($planAmount);
                            $totalAmount = $planAmount + $taxAmount;
                        }
                    } else {
                        // Fixed tax amount
                        if (!$tax->is_inclusive) {
                            $taxAmount = $tax->rate;
                            $totalAmount = $planAmount + $taxAmount;
                        }
                    }
                }

                // Create the invoice
                $invoice = SubscriptionInvoice::create([
                    'tenant_id' => $subscription->tenant_id,
                    'subscription_id' => $subscription->id,
                    'invoice_number' => SubscriptionInvoice::generateInvoiceNumber(),
                    'invoice_date' => Carbon::today(),
                    'due_date' => $subscription->end_date,
                    'amount' => $totalAmount,
                    'subtotal_amount' => $subtotalAmount,
                    'tax_amount' => $taxAmount,
                    'tax_rate' => $tax?->rate,
                    'tax_name' => $tax?->name,
                    'tax_type' => $tax?->type,
                    'tax_inclusive' => $tax?->is_inclusive ?? false,
                    'tax_id' => $tax?->id,
                    'status' => 'pending',
                    'notes' => "Invoice for {$subscription->plan->name} subscription renewal ({$nextStartDate->format('M d, Y')} - {$nextEndDate->format('M d, Y')})",
                ]);

                $count++;
                
                $this->line("✓ Invoice #{$invoice->invoice_number} created for {$subscription->tenant->name} (Subscription #{$subscription->id})");
                
            } catch (\Exception $e) {
                $this->error("Failed to create invoice for Subscription #{$subscription->id}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->newLine();
        $this->info("Invoice generation complete:");
        $this->info("- Created: {$count} invoice(s)");
        
        if ($errors > 0) {
            $this->warn("- Errors: {$errors}");
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Calculate the next end date based on billing cycle.
     */
    private function calculateNextEndDate(Subscription $subscription, Carbon $startDate): Carbon
    {
        return match ($subscription->billing_cycle) {
            'monthly' => $startDate->copy()->addMonth()->subDay(),
            'quarterly' => $startDate->copy()->addMonths(3)->subDay(),
            'semi_annually' => $startDate->copy()->addMonths(6)->subDay(),
            'annually' => $startDate->copy()->addYear()->subDay(),
            default => $startDate->copy()->addMonth()->subDay(),
        };
    }

    /**
     * Get the plan amount based on billing cycle.
     */
    private function getPlanAmount(Subscription $subscription): ?float
    {
        $plan = $subscription->plan;
        
        if (!$plan) {
            return null;
        }

        return match ($subscription->billing_cycle) {
            'monthly' => $plan->monthly_price,
            'quarterly' => $plan->quarterly_price ?? ($plan->monthly_price * 3),
            'semi_annually' => $plan->semi_annual_price ?? ($plan->monthly_price * 6),
            'annually' => $plan->annual_price ?? ($plan->monthly_price * 12),
            default => $plan->monthly_price,
        };
    }
}
