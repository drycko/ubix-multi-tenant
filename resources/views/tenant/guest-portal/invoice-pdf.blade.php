<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Invoice {{ $invoice->invoice_number }}</title>
  <style>
    body { font-family: Arial, sans-serif; font-size: 14px; color: #333; }
    .header { text-align: center; margin-bottom: 30px; }
    .invoice-title { color: #28a745; font-size: 24px; font-weight: bold; }
    .invoice-number { font-size: 18px; margin-top: 10px; }
    .info-section { margin-bottom: 20px; }
    .info-section h4 { color: #666; font-size: 12px; margin-bottom: 5px; }
    .info-section p { margin: 2px 0; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background-color: #f8f9fa; font-weight: bold; }
    .text-right { text-align: right; }
    .totals-table { width: 50%; margin-left: auto; }
    .total-row { font-size: 16px; font-weight: bold; background-color: #f8f9fa; }
    .balance-due { color: #dc3545; }
    .paid { color: #28a745; }
    .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
  </style>
</head>
<body>
  <!-- Header -->
  <div class="header">
    <div class="invoice-title">INVOICE</div>
    <div class="invoice-number">{{ $invoice->invoice_number }}</div>
  </div>

  <!-- Company & Guest Info -->
  <table style="border: none;">
    <tr style="border: none;">
      <td style="border: none; width: 50%; vertical-align: top;">
        <div class="info-section">
          <h4>FROM:</h4>
          <p><strong>{{ $invoice->booking->property->name ?? 'Property' }}</strong></p>
          @if($invoice->booking->property)
          <p>{{ $invoice->booking->property->street_address ?? '' }}</p>
          <p>{{ $invoice->booking->property->city ?? '' }}, {{ $invoice->booking->property->province ?? '' }} {{ $invoice->booking->property->postal_code ?? '' }}</p>
          @endif
        </div>
      </td>
      <td style="border: none; width: 50%; vertical-align: top;">
        <div class="info-section">
          <h4>BILL TO:</h4>
          @if($invoice->booking->guests && $invoice->booking->guests->count() > 0)
          @php $primaryGuest = $invoice->booking->guests->first(); @endphp
          <p><strong>{{ $primaryGuest->full_name }}</strong></p>
          <p>{{ $primaryGuest->email }}</p>
          <p>{{ $primaryGuest->phone_number ?? '' }}</p>
          @endif
        </div>
      </td>
    </tr>
  </table>

  <!-- Invoice Details -->
  <div class="info-section">
    <p><strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($invoice->created_at)->format('M d, Y') }}</p>
    <p><strong>Booking Code:</strong> {{ $invoice->booking->bcode }}</p>
    <p><strong>Room:</strong> {{ $invoice->booking->room->type->name ?? 'N/A' }} - Room {{ $invoice->booking->room->number ?? 'N/A' }}</p>
    <p><strong>Stay Period:</strong> {{ \Carbon\Carbon::parse($invoice->booking->arrival_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($invoice->booking->departure_date)->format('M d, Y') }}</p>
  </div>

  <!-- Items -->
  <table>
    <thead>
      <tr>
        <th>Description</th>
        <th class="text-right">Quantity</th>
        <th class="text-right">Rate</th>
        <th class="text-right">Amount</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          <strong>{{ $invoice->booking->room->type->name ?? 'Room' }}</strong> - Room {{ $invoice->booking->room->number ?? 'N/A' }}
        </td>
        <td class="text-right">{{ $invoice->booking->nights }} night(s)</td>
        <td class="text-right">{{ tenant_currency() }} {{ number_format($invoice->booking->daily_rate, 2) }}</td>
        <td class="text-right">{{ tenant_currency() }} {{ number_format($invoice->subtotal_amount, 2) }}</td>
      </tr>
    </tbody>
  </table>

  <!-- Totals -->
  <table class="totals-table">
    <tr>
      <td><strong>Subtotal:</strong></td>
      <td class="text-right">{{ tenant_currency() }} {{ number_format($invoice->subtotal_amount, 2) }}</td>
    </tr>
    @if($invoice->tax_amount > 0)
    <tr>
      <td><strong>Tax ({{ $invoice->tax->rate ?? 0 }}%):</strong></td>
      <td class="text-right">{{ tenant_currency() }} {{ number_format($invoice->tax_amount, 2) }}</td>
    </tr>
    @endif
    <tr class="total-row">
      <td><strong>Total:</strong></td>
      <td class="text-right"><strong>{{ tenant_currency() }} {{ number_format($invoice->amount, 2) }}</strong></td>
    </tr>
    <tr class="paid">
      <td><strong>Paid:</strong></td>
      <td class="text-right">{{ tenant_currency() }} {{ number_format($invoice->total_paid, 2) }}</td>
    </tr>
    @if($invoice->total_refunded > 0)
    <tr>
      <td><strong>Refunded:</strong></td>
      <td class="text-right">{{ tenant_currency() }} {{ number_format($invoice->total_refunded, 2) }}</td>
    </tr>
    @endif
    <tr class="total-row {{ $invoice->remaining_balance > 0 ? 'balance-due' : 'paid' }}">
      <td><strong>Balance Due:</strong></td>
      <td class="text-right"><strong>{{ tenant_currency() }} {{ number_format($invoice->remaining_balance, 2) }}</strong></td>
    </tr>
  </table>

  <!-- Payment History -->
  @if($invoice->invoicePayments && $invoice->invoicePayments->count() > 0)
  <h4 style="margin-top: 30px;">Payment History</h4>
  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Payment Method</th>
        <th>Reference</th>
        <th class="text-right">Amount</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->invoicePayments as $payment)
      <tr>
        <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y H:i') }}</td>
        <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
        <td>{{ $payment->payment_reference ?? 'N/A' }}</td>
        <td class="text-right">{{ tenant_currency() }} {{ number_format($payment->amount_paid, 2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @endif

  <!-- Footer -->
  <div class="footer">
    <p>Thank you for your business!</p>
    <p>If you have any questions about this invoice, please contact our support team.</p>
  </div>
</body>
</html>
