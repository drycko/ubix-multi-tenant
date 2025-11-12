# Guest Portal Invoice Integration

## Overview
Updated the guest portal invoice functionality to reuse existing booking-invoice views instead of maintaining duplicate custom views. This ensures consistency across admin and guest portal invoice displays while maintaining proper access control.

## Changes Made

### 1. GuestPortalController Updates

#### `viewInvoice()` Method
**Before:** Used custom `tenant.guest-portal.invoice` view with limited data preparation.

**After:** Reuses `tenant.booking-invoices.public-view` with full data preparation:
- Proper eager loading of relationships: `booking.room.type`, `booking.package`, `booking.bookingGuests.guest`, `booking.property`, `tax`
- Tax breakdown calculation via `$bookingInvoice->tax_breakdown`
- Payment gateway configuration (PayFast, PayGate, etc.)
- PayFast form generation for direct payment
- Property and currency data via helper functions

**Benefits:**
- Consistent invoice display across admin and guest portals
- Integrated payment functionality
- Single source of truth for invoice rendering
- Automatic updates when booking-invoice views are improved

#### `downloadInvoice()` Method
**Before:** Used custom `tenant.guest-portal.invoice-pdf` view.

**After:** Reuses `tenant.booking-invoices.pdf` with proper data:
- Same data preparation as view method
- Consistent PDF formatting with admin invoices
- Tax breakdown included
- Property and currency information

**Benefits:**
- Uniform PDF format across system
- Reduced maintenance overhead
- Consistent branding and layout

### 2. Public View Template Updates

#### File: `resources/views/tenant/booking-invoices/public-view.blade.php`

**Print Actions Section:**
```blade
@if(auth()->check())
    <!-- Admin user buttons -->
    <a href="{{ route('tenant.booking-invoices.download', $bookingInvoice) }}">Download PDF</a>
    <a href="{{ route('tenant.booking-invoices.show', $bookingInvoice) }}">Back to Invoice</a>
@elseif(session('guest_id'))
    <!-- Guest portal user buttons -->
    <a href="{{ route('tenant.guest-portal.invoices.download', $bookingInvoice->id) }}">Download PDF</a>
    <a href="{{ route('tenant.guest-portal.invoices') }}">Back to My Invoices</a>
@endif
```

**Features:**
- Detects user type (admin via `auth()->check()` or guest via `session('guest_id')`)
- Shows appropriate navigation buttons based on user context
- Proper route references for each user type
- Download PDF available for both admin and guest users

### 3. Data Preparation

Both `viewInvoice()` and `downloadInvoice()` now prepare complete data:

```php
// Eager load all required relationships
$bookingInvoice = BookingInvoice::with([
    'booking.room.type',
    'booking.package',
    'booking.bookingGuests.guest',
    'booking.property',
    'tax'
])->findOrFail($id);

// Property and currency
$property = current_property();
$currency = property_currency();

// Tax breakdown
$bookingInvoice->taxes = $bookingInvoice->tax_breakdown;

// Payment gateway configuration
$paymentMethods = BookingInvoice::supportedGateways();
$defaultPaymentMethod = BookingInvoice::defaultPaymentGateway() ?? config('payment.default_gateway');

// PayFast form generation
$payFastForm = app(PayfastGatewayService::class)->buildPayfastForm($bookingInvoice);
```

### 4. Invoice Display Features

The `public-view.blade.php` template includes:

**Header Section:**
- Property name, address, phone, email
- Invoice number, date, booking code
- Status badge (paid, pending, partially paid, cancelled)
- Paid amount and remaining balance (if applicable)
- Integrated payment button (PayFast/PayGate) for outstanding balance

**Bill To Section:**
- Primary guest information
- Email, phone, physical address
- Nationality

**Booking Information:**
- Check-in and check-out dates (formatted)
- Duration in nights
- Booking type (shared/private room)

**Services Table:**
- Room number and type
- Package information (if applicable)
- Shared room indicator
- Check-in/out dates, nights, daily rate
- Total amount calculation

**Totals Section:**
- Subtotal (if taxes apply)
- Tax breakdown (name, rate, amount)
- Total amount
- Amount paid (if any)
- Total refunded (if any)
- Balance due

**Special Requests:**
- Guest-specific special requests display

**Footer:**
- Thank you message
- Print timestamp
- System branding

**Print Actions:**
- Print button (JavaScript-based)
- Download PDF link
- Back navigation (context-aware)
- Hidden during actual printing via `@media print`

### 5. Access Control

**Guest Portal Routes** (`routes/tenant.php`):
```php
Route::get('/invoices/{id}', [GuestPortalController::class, 'viewInvoice'])
    ->name('tenant.guest-portal.invoices.show');
    
Route::get('/invoices/{id}/download', [GuestPortalController::class, 'downloadInvoice'])
    ->name('tenant.guest-portal.invoices.download');
```

**Security Measures:**
- Guest authentication check via `$this->getGuest()`
- Invoice access verification via `whereHas('booking.guests')` query
- Only shows invoices for bookings where guest is listed
- Redirect to login if not authenticated

### 6. Routes Referenced

**Guest Portal Routes:**
- `tenant.guest-portal.invoices` - Invoice list page
- `tenant.guest-portal.invoices.show` - View invoice (uses public-view)
- `tenant.guest-portal.invoices.download` - Download PDF

**Admin Routes:**
- `tenant.booking-invoices.show` - Admin invoice detail
- `tenant.booking-invoices.download` - Admin PDF download

**Payment Routes:**
- `tenant.payfast.initiate` - PayFast payment initiation
- `tenant.paygate.initiate` - PayGate payment initiation

## Files Modified

1. **app/Http/Controllers/Tenant/GuestPortalController.php**
   - `viewInvoice()` method - Updated to use public-view template
   - `downloadInvoice()` method - Updated to use pdf template

2. **resources/views/tenant/booking-invoices/public-view.blade.php**
   - Added guest portal authentication check
   - Added guest-specific navigation buttons
   - Context-aware back button routing

## Files to Potentially Remove

Now that we're using the shared invoice views, these custom guest portal invoice views are no longer needed:

- `resources/views/tenant/guest-portal/invoice.blade.php` (replaced by public-view)
- `resources/views/tenant/guest-portal/invoice-pdf.blade.php` (replaced by pdf)

**Recommendation:** Keep them temporarily for rollback capability, then remove after testing confirms everything works correctly.

## Testing Checklist

### Guest Portal Testing
- [ ] Guest can view invoice list
- [ ] Guest can click to view invoice detail
- [ ] Invoice displays correct booking information
- [ ] Invoice shows correct amounts and tax breakdown
- [ ] Payment button appears for unpaid/partially paid invoices
- [ ] Download PDF button works correctly
- [ ] PDF contains all correct information
- [ ] Back button navigates to invoice list
- [ ] Guest cannot access other guests' invoices
- [ ] Unauthenticated users redirected to login

### Admin Portal Testing
- [ ] Admin invoice view still works correctly
- [ ] Admin PDF download still works
- [ ] Admin back button navigates correctly
- [ ] No interference between guest and admin routes

### Payment Integration Testing
- [ ] PayFast form displays for South African properties
- [ ] PayGate form displays when configured
- [ ] Payment button only shows for outstanding balances
- [ ] Payment initiation works correctly from guest portal

### Print Functionality Testing
- [ ] Print button works on all browsers
- [ ] Print preview shows correct formatting
- [ ] Print actions hidden in print mode
- [ ] All invoice data visible in print output

## Benefits of Integration

1. **Reduced Code Duplication:** Single invoice view instead of separate admin and guest versions
2. **Consistency:** Same invoice format across all user types
3. **Easier Maintenance:** Updates to invoice layout apply everywhere automatically
4. **Feature Parity:** Guest portal inherits all admin invoice features (payment buttons, tax display, etc.)
5. **Better UX:** Consistent experience reduces confusion
6. **Payment Integration:** Built-in payment functionality without extra work
7. **Professional Appearance:** Polished invoice design used throughout system

## Future Enhancements

1. **Email Invoice:** Add "Email Invoice" button for guests to receive copy
2. **Invoice History:** Track when guest views/downloads invoices
3. **Payment History:** Show payment transaction details on invoice
4. **Multi-Currency:** Display amounts in guest's preferred currency
5. **Invoice Disputes:** Allow guests to flag issues directly on invoice
6. **Automatic Reminders:** Send payment reminder emails for overdue invoices

## Technical Notes

### Helper Functions Used
- `current_property()` - Gets active tenant property
- `property_currency()` - Gets property currency symbol/code
- `app(PayfastGatewayService::class)` - Service locator pattern for dependency injection

### Model Methods Used
- `BookingInvoice::supportedGateways()` - Returns configured payment gateways
- `BookingInvoice::defaultPaymentGateway()` - Returns default payment method
- `$bookingInvoice->tax_breakdown` - Accessor for tax calculation details
- `$bookingInvoice->remaining_balance` - Calculates unpaid amount

### Blade Directives
- `@if(auth()->check())` - Checks Laravel admin authentication
- `@elseif(session('guest_id'))` - Checks guest portal authentication
- `@media print` CSS - Hides print buttons when printing

## Related Documentation

- `GUEST_PORTAL_IMPLEMENTATION.md` - Complete guest portal documentation
- `GUEST_PORTAL_NAVIGATION_UPDATE.md` - Navigation system documentation
- Invoice management in admin portal
- Payment gateway configuration guides
