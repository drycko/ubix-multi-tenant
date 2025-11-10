<?php

namespace App\Services;

use App\Models\Tax;

class TaxCalculationService
{
    /**
     * Calculate tax for an invoice amount.
     *
     * @param float $baseAmount The base amount before tax
     * @return array Tax calculation details
     */
    public function calculateTaxForInvoice(float $baseAmount): array
    {
        
        // Get the first active tax by display order
        $tax = Tax::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->first();

        if (!$tax) {
            return [
                'tax_id' => null,
                'tax_name' => null,
                'tax_rate' => 0,
                'tax_type' => null,
                'tax_inclusive' => false,
                'subtotal_amount' => $baseAmount,
                'tax_amount' => 0,
                'total_amount' => $baseAmount,
            ];
        }

        // Calculate tax based on type and inclusive/exclusive setting
        if ($tax->is_inclusive) {
            // Tax is included in the base amount
            $totalAmount = $baseAmount;
            
            if ($tax->type === 'percentage') {
                // Calculate tax from inclusive amount: tax = amount * (rate / (100 + rate))
                $taxAmount = $baseAmount * ($tax->rate / (100 + $tax->rate));
                $subtotalAmount = $baseAmount - $taxAmount;
            } else {
                // Fixed tax amount
                $taxAmount = $tax->rate;
                $subtotalAmount = $baseAmount - $taxAmount;
            }
        } else {
            // Tax is additional to the base amount
            $subtotalAmount = $baseAmount;
            
            if ($tax->type === 'percentage') {
                $taxAmount = $baseAmount * ($tax->rate / 100);
            } else {
                $taxAmount = $tax->rate;
            }
            
            $totalAmount = $subtotalAmount + $taxAmount;
        }

        return [
            'tax_id' => $tax->id,
            'tax_name' => $tax->name,
            'tax_rate' => $tax->rate,
            'tax_type' => $tax->type,
            'tax_inclusive' => $tax->is_inclusive,
            'subtotal_amount' => round($subtotalAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'total_amount' => round($totalAmount, 2),
        ];
    }

    /**
     * Calculate tax for a booking based on daily rate and nights.
     *
     * @param float $dailyRate
     * @param int $qty
     * @return array
     */
    public function calculateTaxForBooking(float $dailyRate, int $qty): array
    {
        $baseAmount = $dailyRate * $qty;
        return $this->calculateTaxForInvoice($baseAmount);
    }

    /**
     * Get active taxes.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveTaxes()
    {
        
        return Tax::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get();
    }

    /**
     * Get the primary tax (first by display order).
     *
     * @return Tax|null
     */
    public function getPrimaryTax(): ?Tax
    {
        
        return Tax::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->first();
    }
}