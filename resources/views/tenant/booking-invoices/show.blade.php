@extends('tenant.layouts.app')

@section('title', 'Invoice ' . $bookingInvoice->invoice_number)

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-file-invoice"></i>
          <small class="text-muted">Invoice Details</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.booking-invoices.index') }}">Invoices</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $bookingInvoice->invoice_number }}</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
  <div class="container-fluid">

    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
      <!-- Main Invoice -->
      <div class="col-md-8">
        <div class="card card-primary card-outline">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-file-invoice"></i> Invoice #{{ $bookingInvoice->invoice_number }}
            </h3>
            <div class="card-tools">
              @php
                $statusColors = [
                  'pending' => 'warning',
                  'paid' => 'success',
                  'partially_paid' => 'info',
                  'cancelled' => 'danger'
                ];
              @endphp
              <span class="badge bg-{{ $statusColors[$bookingInvoice->status] ?? 'secondary' }}">
                {{ ucfirst(str_replace('_', ' ', $bookingInvoice->status)) }}
              </span>
            </div>
          </div>
          <div class="card-body">
            <!-- Invoice Header -->
            <div class="row mb-4">
              <div class="col-md-6">
                <h4 class="text-primary">{{ $property->name }}</h4>
                @if($property->address)
                <p class="mb-1">{{ $property->address }}</p>
                @endif
                @if($property->phone)
                <p class="mb-1"><strong>Phone:</strong> {{ $property->phone }}</p>
                @endif
                @if($property->email)
                <p class="mb-1"><strong>Email:</strong> {{ $property->email }}</p>
                @endif
              </div>
              <div class="col-md-6 text-end">
                <h5>INVOICE</h5>
                <p class="mb-1"><strong>Invoice #:</strong> {{ $bookingInvoice->invoice_number }}</p>
                <p class="mb-1"><strong>Date:</strong> {{ $bookingInvoice->created_at->format('M j, Y') }}</p>
                <p class="mb-1"><strong>Booking:</strong> {{ $bookingInvoice->booking->bcode }}</p>
              </div>
            </div>

            <hr>

            <!-- Guest Information -->
            @php
              $primaryGuest = $bookingInvoice->booking->bookingGuests->where('is_primary', true)->first()?->guest;
            @endphp
            @if($primaryGuest)
            <div class="row mb-4">
              <div class="col-md-6">
                <h6>Bill To:</h6>
                <p class="mb-1"><strong>{{ $primaryGuest->first_name }} {{ $primaryGuest->last_name }}</strong></p>
                @if($primaryGuest->email)
                <p class="mb-1">{{ $primaryGuest->email }}</p>
                @endif
                @if($primaryGuest->phone)
                <p class="mb-1">{{ $primaryGuest->phone }}</p>
                @endif
                @if($primaryGuest->physical_address)
                <p class="mb-1">{{ $primaryGuest->physical_address }}</p>
                @endif
              </div>
            </div>
            <hr>
            @endif

            <!-- Booking Details -->
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead class="table-light">
                  <tr>
                    <th>Description</th>
                    <th class="text-center">Dates</th>
                    <th class="text-center">Nights</th>
                    <th class="text-end">Rate</th>
                    <th class="text-end">Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <strong>Room {{ $bookingInvoice->booking->room->number }}</strong>
                      <br><small class="text-muted">{{ $bookingInvoice->booking->room->type->name }}</small>
                      @if($bookingInvoice->booking->package)
                      <br><small class="text-info">Package: {{ $bookingInvoice->booking->package->pkg_name }}</small>
                      @endif
                      @if($bookingInvoice->booking->is_shared)
                      <br><small class="text-warning">Shared Room</small>
                      @endif
                    </td>
                    <td class="text-center">
                      {{ $bookingInvoice->booking->arrival_date->format('M j') }} - 
                      {{ $bookingInvoice->booking->departure_date->format('M j, Y') }}
                    </td>
                    <td class="text-center">{{ $bookingInvoice->booking->nights }}</td>
                    <td class="text-end">{{ $currency }} {{ number_format($bookingInvoice->booking->daily_rate, 2) }}</td>
                    <td class="text-end">{{ $currency }} {{ number_format($bookingInvoice->amount, 2) }}</td>
                  </tr>
                </tbody>
                <tfoot>
                  @if($bookingInvoice->taxes)
                  <tr class="text-muted">
                    <td colspan="4" class="text-end">Subtotal:</td>
                    <td class="text-end">{{ $currency }} {{ number_format($bookingInvoice->taxes['subtotal'], 2) }}</td>
                  </tr>
                  <tr class="text-muted">
                    <td colspan="4" class="text-end">Tax ({{ $bookingInvoice->taxes['name'] }} {{ $bookingInvoice->taxes['rate'] }}%):</td>
                    <td class="text-end">{{ $currency }} {{ number_format($bookingInvoice->taxes['amount'], 2) }}</td>
                  </tr>
                  @endif
                  <tr class="text-muted">
                    <th colspan="4" class="text-end">Total:</th>
                    <th class="text-end">{{ $currency }} {{ number_format($bookingInvoice->amount, 2) }}</th>
                  </tr>
                  @if ($bookingInvoice->total_paid > 0)
                  <tr>
                    <td colspan="4" class="text-end">Total Payments:</td>
                    <td class="text-end text-success">- {{ $currency }} {{ number_format($bookingInvoice->total_paid, 2) }}</td>
                  </tr>
                  @endif
                  @if ($bookingInvoice->total_refunded > 0)
                  <tr class="text-danger">
                    <td colspan="4" class="text-end">Total Refunds:</td>
                    <td class="text-end text-danger">- {{ $currency }} {{ number_format($bookingInvoice->total_refunded, 2) }}</td>
                  </tr>
                  @endif
                  <tr class="">
                    <th colspan="4" class="text-end">Balance Remaining:</th>
                    <th class="text-end">{{ $currency }} {{ number_format($bookingInvoice->remaining_balance, 2) }}</th>
                  </tr>
                </tfoot>
              </table>
            </div>

            <!-- Additional Information -->
            @if($bookingInvoice->booking->bookingGuests->whereNotNull('special_requests')->count() > 0)
            <div class="mt-4">
              <h6>Special Requests:</h6>
              @foreach($bookingInvoice->booking->bookingGuests->whereNotNull('special_requests') as $bookingGuest)
              <div class="alert alert-info">
                <strong>{{ $bookingGuest->guest->first_name }} {{ $bookingGuest->guest->last_name }}:</strong>
                {{ $bookingGuest->special_requests }}
              </div>
              @endforeach
            </div>
            @endif

            <!-- Transaction History (we will need to mix these with refunds) -->
            @if($transactions->count() > 0)
            <div class="mt-4">
              <h6>Transaction History:</h6>
              <div class="table-responsive">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Method</th>
                      <th>Type</th>
                      <th>Reference</th>
                      <th>Amount</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($transactions->sortByDesc('created_at') as $transaction)
                    @if($transaction->type === 'refund')
                    <tr class="table-danger">
                    @elseif($transaction->type === 'payment')
                    <tr class="table-success">
                    @endif
                      <td>{{ $transaction->created_at->format('M j, Y') }}</td>
                      <td>{{ $transaction->payment_method_label }}</td>
                      <td>{{ $transaction->type }}</td>
                      <td>{{ $transaction->reference_number ?? '-' }}</td>
                      <td>{{ $currency }} {{ number_format($transaction->amount, 2) }}</td>
                      <td>
                        @if($transaction->type === 'refund')
                        <span class="badge bg-{{ $transaction->status === 'approved' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                          {{ $transaction->status }}
                        </span>
                        @else
                        <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'danger') }}">
                          {{ $transaction->status_label }}
                        </span>
                        @endif
                      </td>
                      <td>
                        <div class="btn-group btn-group-sm">
                          {{-- Do not mix routes --}}
                          @if($transaction->type === 'refund')
                          <a href="{{ route('tenant.refunds.show', $transaction) }}" 
                             class="btn btn-outline-primary btn-sm" title="View Refund">
                            <i class="fas fa-eye"></i>
                          </a>

                          @else
                          <a href="{{ route('tenant.invoice-payments.show', [$bookingInvoice, $transaction]) }}" 
                             class="btn btn-outline-primary btn-sm" title="View Payment">
                            <i class="fas fa-eye"></i>
                          </a>
                          
                          @endif
                        </div>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-md-4">
        <!-- Invoice Actions -->
        <div class="card card-info card-outline mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-tools"></i> Actions
            </h5>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              {{-- record payment modal button - only show if balance remaining --}}
              @if($bookingInvoice->remaining_balance > 0)
              <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                <i class="fas fa-credit-card"></i> Record Payment
              </a>
              @endif
              {{-- view all payments button - only show if payments exist --}}
              @if($bookingInvoice->invoicePayments->count() > 0)
              <a href="{{ route('tenant.invoice-payments.index', $bookingInvoice) }}" class="btn btn-outline-success">
                <i class="fas fa-list"></i> View All Payments ({{ $bookingInvoice->invoicePayments->count() }})
              </a>
              @else
              <a href="#" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#emailInvoiceModal">
                <i class="fas fa-link"></i> Send Invoice via Email
              </a>
              @endif
              <a href="{{ route('tenant.booking-invoices.download', $bookingInvoice) }}" class="btn btn-primary">
                <i class="fas fa-download"></i> Download PDF
              </a>
              <a href="{{ route('tenant.booking-invoices.print', $bookingInvoice) }}" target="_blank" class="btn btn-outline-primary">
                <i class="fas fa-print"></i> Print Invoice
              </a>
              {{-- view invoice as public link --}}
              <a href="{{ route('tenant.booking-invoices.public-view', $bookingInvoice) }}" target="_blank" class="btn btn-outline-warning">
                <i class="fas fa-external-link-alt"></i> View as Guest
              </a>
              <hr>
              <a href="{{ route('tenant.bookings.show', $bookingInvoice->booking) }}" class="btn btn-outline-secondary">
                <i class="fas fa-bed"></i> View Booking
              </a>
              <a href="{{ route('tenant.bookings.download-room-info', $bookingInvoice->booking) }}" class="btn btn-outline-info">
                <i class="fas fa-info-circle"></i> Room Info PDF
              </a>
              <hr>
              <a href="{{ route('tenant.booking-invoices.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Invoices
              </a>
            </div>
          </div>
        </div>

        <!-- Invoice Summary -->
        <div class="card card-warning card-outline mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-info-circle"></i> Invoice Summary
            </h5>
          </div>
          <div class="card-body">
            <table class="table table-sm">
              <tr>
                <td><strong>Invoice #:</strong></td>
                <td>{{ $bookingInvoice->invoice_number }}</td>
              </tr>
              <tr>
                <td><strong>Status:</strong></td>
                <td>
                  <span class="badge bg-{{ $statusColors[$bookingInvoice->status] ?? 'secondary' }}">
                    {{ ucfirst(str_replace('_', ' ', $bookingInvoice->status)) }}
                  </span>
                </td>
              </tr>
              <tr>
                <td><strong>Total Amount:</strong></td>
                <td><strong>{{ $currency }} {{ number_format($bookingInvoice->amount, 2) }}</strong></td>
              </tr>
              <tr>
                <td><strong>Amount Paid:</strong></td>
                <td><strong class="text-success">{{ $currency }} {{ number_format($bookingInvoice->total_paid, 2) }}</strong></td>
              </tr>
              <tr>
                <td><strong>Balance Due:</strong></td>
                <td>
                  <strong class="{{ $bookingInvoice->remaining_balance > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $currency }} {{ number_format($bookingInvoice->remaining_balance, 2) }}
                  </strong>
                </td>
              </tr>
              <tr>
                <td><strong>Created:</strong></td>
                <td>{{ $bookingInvoice->created_at->format('M j, Y g:i A') }}</td>
              </tr>
              @if($bookingInvoice->updated_at != $bookingInvoice->created_at)
              <tr>
                <td><strong>Updated:</strong></td>
                <td>{{ $bookingInvoice->updated_at->format('M j, Y g:i A') }}</td>
              </tr>
              @endif
            </table>
          </div>
        </div>

        <!-- Booking Summary -->
        <div class="card card-success card-outline">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-bed"></i> Booking Summary
            </h5>
          </div>
          <div class="card-body">
            <table class="table table-sm">
              <tr>
                <td><strong>Booking:</strong></td>
                <td>{{ $bookingInvoice->booking->bcode }}</td>
              </tr>
              <tr>
                <td><strong>Room:</strong></td>
                <td>{{ $bookingInvoice->booking->room->number }} ({{ $bookingInvoice->booking->room->type->name }})</td>
              </tr>
              <tr>
                <td><strong>Guest:</strong></td>
                <td>
                  @if($primaryGuest)
                    {{ $primaryGuest->first_name }} {{ $primaryGuest->last_name }}
                  @else
                    <span class="text-muted">No guest</span>
                  @endif
                </td>
              </tr>
              <tr>
                <td><strong>Check-in:</strong></td>
                <td>{{ $bookingInvoice->booking->arrival_date->format('M j, Y') }}</td>
              </tr>
              <tr>
                <td><strong>Check-out:</strong></td>
                <td>{{ $bookingInvoice->booking->departure_date->format('M j, Y') }}</td>
              </tr>
              <tr>
                <td><strong>Nights:</strong></td>
                <td>{{ $bookingInvoice->booking->nights }}</td>
              </tr>
              <tr>
                <td><strong>Status:</strong></td>
                <td>
                  <span class="badge bg-{{ $bookingInvoice->booking->status === 'confirmed' ? 'success' : ($bookingInvoice->booking->status === 'pending' ? 'warning' : 'info') }}">
                    {{ ucfirst(str_replace('_', ' ', $bookingInvoice->booking->status)) }}
                  </span>
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--end::App Content-->

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="recordPaymentModalLabel">
          <i class="fas fa-credit-card"></i> Record Payment - Invoice {{ $bookingInvoice->invoice_number }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @php
          $guestId = $primaryGuest ? $primaryGuest->id : null;
        @endphp
        @include('tenant.invoice-payments.partials.payment-form')
      </div>
    </div>
  </div>
</div>

<!-- Email Invoice Modal -->
<div class="modal fade" id="emailInvoiceModal" tabindex="-1" aria-labelledby="emailInvoiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="emailInvoiceModalLabel">
          <i class="fas fa-envelope"></i> Email Invoice - Invoice {{ $bookingInvoice->invoice_number }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @include('tenant.booking-invoices.partials.email-form')
      </div>
    </div>
  </div>
</div>

@endsection