@extends('tenant.layouts.app')

@section('title', 'Send Room Information')

@section('content')
<!--begin::App Content Header-->
<div class="app-content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6">
        <h3 class="mb-0">
          <i class="fas fa-paper-plane"></i>
          <small class="text-muted">Send Room Information</small>
        </h3>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.bookings.index') }}">Bookings</a></li>
          <li class="breadcrumb-item"><a href="{{ route('tenant.bookings.show', $booking) }}">{{ $booking->bcode }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">Send Room Info</li>
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

    <div class="row">
      <!-- Main Form -->
      <div class="col-md-8">
        <div class="card card-primary card-outline">
          <div class="card-header">
            <h3 class="card-title">
              <i class="fas fa-envelope"></i> Send Room Information
            </h3>
            <div class="card-tools">
              <span class="badge bg-info">{{ $booking->bcode }}</span>
            </div>
          </div>
          <div class="card-body">
            <form action="#" method="POST" id="sendRoomInfoForm">
              @csrf
              
              <div class="alert alert-info">
                <h6 class="alert-heading">
                  <i class="fas fa-info-circle"></i> Send Room Information
                </h6>
                <p class="mb-0">
                  This will send the room information PDF to the primary guest's email address. 
                  The PDF will include booking details, room information, and guest information.
                </p>
              </div>

              <!-- Email Recipients -->
              <div class="mb-3">
                <label for="recipients" class="form-label">Email Recipients <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                  <input type="email" 
                         class="form-control" 
                         id="recipients" 
                         name="recipients" 
                         value="{{ $booking->bookingGuests->where('is_primary', true)->first()?->guest?->email }}"
                         required
                         placeholder="Enter email addresses separated by commas">
                </div>
                <div class="form-text">
                  Enter multiple email addresses separated by commas if needed.
                </div>
              </div>

              <!-- Subject Line -->
              <div class="mb-3">
                <label for="subject" class="form-label">Email Subject <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control" 
                       id="subject" 
                       name="subject" 
                       value="Room Information for Booking {{ $booking->bcode }} - {{ $property->name }}"
                       required>
              </div>

              <!-- Email Message -->
              <div class="mb-3">
                <label for="message" class="form-label">Email Message</label>
                <textarea class="form-control" 
                          id="message" 
                          name="message" 
                          rows="5"
                          placeholder="Add a personal message (optional)">Dear {{ $booking->bookingGuests->where('is_primary', true)->first()?->guest?->first_name }},

Please find attached your room information for booking {{ $booking->bcode }}.

We look forward to welcoming you to {{ $property->name }}.

Best regards,
{{ $property->name }} Team</textarea>
              </div>

              <!-- Include Options -->
              <div class="mb-3">
                <label class="form-label">Include Additional Information</label>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="include_contact" name="include_contact" checked>
                  <label class="form-check-label" for="include_contact">
                    Include property contact information
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="include_directions" name="include_directions">
                  <label class="form-check-label" for="include_directions">
                    Include directions to property
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="include_policies" name="include_policies">
                  <label class="form-check-label" for="include_policies">
                    Include property policies
                  </label>
                </div>
              </div>

              <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="button" class="btn btn-outline-primary" id="previewBtn">
                  <i class="fas fa-eye"></i> Preview PDF
                </button>
                <button type="submit" class="btn btn-primary" id="sendBtn">
                  <span id="spinner" class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                  <i class="fas fa-paper-plane"></i> Send Email
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-md-4">
        <!-- Booking Summary -->
        <div class="card card-info card-outline mb-3">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="fas fa-info-circle"></i> Booking Summary
            </h5>
          </div>
          <div class="card-body">
            <table class="table table-sm">
              <tr>
                <td><strong>Booking Code:</strong></td>
                <td>{{ $booking->bcode }}</td>
              </tr>
              <tr>
                <td><strong>Guest:</strong></td>
                <td>{{ $booking->bookingGuests->where('is_primary', true)->first()?->guest?->first_name }} {{ $booking->bookingGuests->where('is_primary', true)->first()?->guest?->last_name }}</td>
              </tr>
              <tr>
                <td><strong>Room:</strong></td>
                <td>{{ $booking->room->number }} ({{ $booking->room->type->name }})</td>
              </tr>
              <tr>
                <td><strong>Check-in:</strong></td>
                <td>{{ $booking->arrival_date->format('M j, Y') }}</td>
              </tr>
              <tr>
                <td><strong>Check-out:</strong></td>
                <td>{{ $booking->departure_date->format('M j, Y') }}</td>
              </tr>
              <tr>
                <td><strong>Nights:</strong></td>
                <td>{{ $booking->nights }}</td>
              </tr>
              <tr>
                <td><strong>Status:</strong></td>
                <td>
                  <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'info') }}">
                    {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                  </span>
                </td>
              </tr>
            </table>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
          <div class="card-body">
            <div class="d-grid gap-2">
              <a href="{{ route('tenant.bookings.download-room-info', $booking) }}" class="btn btn-outline-secondary">
                <i class="fas fa-download"></i> Download PDF
              </a>
              <a href="{{ route('tenant.bookings.show', $booking) }}" class="btn btn-outline-primary">
                <i class="fas fa-eye"></i> View Booking
              </a>
              <a href="{{ route('tenant.bookings.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Bookings
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--end::App Content-->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview PDF functionality
    document.getElementById('previewBtn').addEventListener('click', function() {
        window.open('{{ route("tenant.bookings.download-room-info", $booking) }}', '_blank');
    });

    // Form submission
    document.getElementById('sendRoomInfoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const sendBtn = document.getElementById('sendBtn');
        const spinner = document.getElementById('spinner');
        
        // Show loading state
        sendBtn.disabled = true;
        spinner.classList.remove('d-none');
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';
        
        // Simulate sending (replace with actual implementation)
        setTimeout(function() {
            alert('Email functionality would be implemented here.\n\nFor now, you can download the PDF using the "Download PDF" button.');
            
            // Reset button state
            sendBtn.disabled = false;
            spinner.classList.add('d-none');
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Email';
        }, 2000);
    });
});
</script>
@endsection