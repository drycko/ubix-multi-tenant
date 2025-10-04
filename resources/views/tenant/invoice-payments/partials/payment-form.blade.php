<form action="{{ route('tenant.invoice-payments.store', $bookingInvoice) }}" method="POST" id="paymentForm">
    @csrf
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
            <select name="payment_method" id="payment_method" class="form-select" required>
                <option value="">Select payment method</option>
                @foreach($paymentMethods as $key => $label)
                    <option value="{{ $key }}" {{ old('payment_method') == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('payment_method')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="col-md-6 mb-3">
            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">{{ $currency }}</span>
                <input type="number" 
                       name="amount" 
                       id="amount" 
                       class="form-control" 
                       step="0.01" 
                       min="0.01" 
                       max="{{ $bookingInvoice->remaining_balance }}"
                       value="{{ old('amount', $bookingInvoice->remaining_balance) }}" 
                       required>
            </div>
            <small class="text-muted">
                Remaining balance: {{ $currency }} {{ number_format($bookingInvoice->remaining_balance, 2) }}
            </small>
            @error('amount')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
            <input type="date" 
                   name="payment_date" 
                   id="payment_date" 
                   class="form-control" 
                   value="{{ old('payment_date', date('Y-m-d')) }}" 
                   max="{{ date('Y-m-d') }}"
                   required>
            @error('payment_date')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="col-md-6 mb-3">
            <label for="reference_number" class="form-label">Reference Number</label>
            <input type="text" 
                   name="reference_number" 
                   id="reference_number" 
                   class="form-control" 
                   value="{{ old('reference_number') }}" 
                   placeholder="Transaction ID, Check #, etc.">
            @error('reference_number')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="mb-3">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" id="status" class="form-select" required>
            <option value="completed" {{ old('status', 'completed') == 'completed' ? 'selected' : '' }}>
                Completed
            </option>
            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>
                Pending
            </option>
        </select>
        @error('status')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="mb-3">
        <label for="notes" class="form-label">Notes</label>
        <textarea name="notes" 
                  id="notes" 
                  class="form-control" 
                  rows="3" 
                  placeholder="Additional payment details...">{{ old('notes') }}</textarea>
        @error('notes')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <input type="hidden" name="guest_id" value="{{ $guestId }}">
    
    <div class="alert alert-info">
        <strong>Invoice Summary:</strong><br>
        <div class="row">
            <div class="col-6">Invoice Amount:</div>
            <div class="col-6 text-end">{{ $currency }} {{ number_format($bookingInvoice->amount, 2) }}</div>
        </div>
        <div class="row">
            <div class="col-6">Total Paid:</div>
            <div class="col-6 text-end">{{ $currency }} {{ number_format($bookingInvoice->total_paid, 2) }}</div>
        </div>
        <div class="row">
            <div class="col-6"><strong>Remaining:</strong></div>
            <div class="col-6 text-end"><strong>{{ $currency }} {{ number_format($bookingInvoice->remaining_balance, 2) }}</strong></div>
        </div>
    </div>
    
    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-credit-card"></i> Record Payment
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quick amount buttons
    const amountInput = document.getElementById('amount');
    const remainingBalance = {{ $bookingInvoice->remaining_balance }};
    
    // Add quick amount buttons
    const amountGroup = amountInput.closest('.input-group');
    if (amountGroup && remainingBalance > 0) {
        const quickButtons = document.createElement('div');
        quickButtons.className = 'mt-1';
        quickButtons.innerHTML = `
            <small class="text-muted">Quick amounts: </small>
            ${remainingBalance >= 50 ? '<button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="setAmount(50)">50</button>' : ''}
            ${remainingBalance >= 100 ? '<button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="setAmount(100)">100</button>' : ''}
            ${remainingBalance >= 200 ? '<button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="setAmount(200)">200</button>' : ''}
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="setAmount(${remainingBalance})">Full Amount</button>
        `;
        amountGroup.parentNode.insertBefore(quickButtons, amountGroup.nextSibling);
    }
    
    // Payment method dependent fields
    const paymentMethodSelect = document.getElementById('payment_method');
    const referenceField = document.getElementById('reference_number');
    
    paymentMethodSelect.addEventListener('change', function() {
        const method = this.value;
        const referenceLabel = referenceField.previousElementSibling;
        
        switch(method) {
            case 'card':
                referenceLabel.textContent = 'Transaction ID';
                referenceField.placeholder = 'Card transaction reference';
                break;
            case 'bank_transfer':
                referenceLabel.textContent = 'Transfer Reference';
                referenceField.placeholder = 'Bank transfer reference';
                break;
            case 'check':
                referenceLabel.textContent = 'Check Number';
                referenceField.placeholder = 'Check number';
                break;
            case 'mobile_money':
                referenceLabel.textContent = 'Transaction ID';
                referenceField.placeholder = 'Mobile money transaction ID';
                break;
            default:
                referenceLabel.textContent = 'Reference Number';
                referenceField.placeholder = 'Transaction ID, Check #, etc.';
        }
    });
});

function setAmount(amount) {
    document.getElementById('amount').value = amount;
}
</script>