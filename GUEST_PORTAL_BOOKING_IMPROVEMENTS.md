# Guest Portal Booking Improvements

## Overview
Enhanced the guest portal booking functionality to align with tenant booking views, added download/print capabilities, and improved the overall user experience. Guests can now download and print their booking information in a professional PDF format.

## Changes Made

### 1. GuestPortalController Updates

#### New Method: `downloadBookingInfo()`
**Location:** `app/Http/Controllers/Tenant/GuestPortalController.php`

**Purpose:** Generate and download booking information as PDF

**Implementation:**
```php
public function downloadBookingInfo($id)
{
    $guest = $this->getGuest();
    
    if (!$guest) {
        return redirect()->route('tenant.guest-portal.login');
    }

    $booking = \App\Models\Tenant\Booking::whereHas('guests', function($q) use ($guest) {
            $q->where('guest_id', $guest->id);
        })
        ->with(['room.type', 'package', 'bookingGuests.guest', 'property'])
        ->findOrFail($id);

    // Prepare data for PDF
    $property = current_property();
    $currency = property_currency();

    // Generate PDF from existing tenant booking view
    $pdf = \PDF::loadView('tenant.bookings.room-info-pdf', compact('booking', 'property', 'currency'));
    
    return $pdf->download("booking-{$booking->bcode}.pdf");
}
```

**Features:**
- Reuses existing `tenant.bookings.room-info-pdf` template for consistency
- Security check ensures guests can only download their own bookings
- Proper eager loading of relationships
- Professional PDF filename using booking code

#### Updated Method: `showBooking()`
**Changes:**
- Changed `'guests'` to `'bookingGuests.guest'` for proper relationship loading
- This aligns with the booking-guest structure used throughout the system

**Before:**
```php
->with([
    'room.type.amenities', 
    'property', 
    'invoices.invoicePayments', 
    'guests',  // Incorrect relationship
    'package',
    ...
])
```

**After:**
```php
->with([
    'room.type.amenities', 
    'property', 
    'invoices.invoicePayments',
    'bookingGuests.guest',  // Correct relationship
    'package',
    ...
])
```

### 2. Route Addition

**File:** `routes/tenant.php`

**New Route:**
```php
Route::get('/bookings/{booking}/download', [GuestPortalController::class, 'downloadBookingInfo'])
    ->name('tenant.guest-portal.bookings.download');
```

**Location:** Guest portal authenticated routes group (`guest.portal` middleware)

**Full Context:**
```php
// Booking Management
Route::get('/bookings', [GuestPortalController::class, 'myBookings'])
    ->name('tenant.guest-portal.bookings');
Route::get('/bookings/{booking}', [GuestPortalController::class, 'showBooking'])
    ->name('tenant.guest-portal.bookings.show');
Route::get('/bookings/{booking}/download', [GuestPortalController::class, 'downloadBookingInfo'])
    ->name('tenant.guest-portal.bookings.download');  // NEW
Route::post('/bookings/{booking}/cancel', [GuestPortalController::class, 'cancelBooking'])
    ->name('tenant.guest-portal.bookings.cancel');
```

### 3. Booking Detail View Enhancements

**File:** `resources/views/tenant/guest-portal/booking-detail.blade.php`

#### A. Enhanced Page Header
**Added:**
- Status badge display in header (colored based on booking status)
- Download Info button with target="_blank" for PDF in new tab
- Improved responsive layout with flexbox wrapping

**Status Badge Colors:**
- `pending` → Secondary (gray)
- `booked` → Primary (blue)
- `confirmed` → Warning (yellow)
- `checked_in` → Success (green)
- `checked_out` → Info (cyan)
- `completed` → Primary (blue)
- `cancelled` → Danger (red)
- `no_show` → Dark (black)

**Before:**
```blade
<p class="text-muted mb-0">Booking Code: <strong>{{ $booking->bcode }}</strong></p>
```

**After:**
```blade
<p class="text-muted mb-0">
    Booking Code: <strong>{{ $booking->bcode }}</strong>
    <span class="ms-3">
        <span class="badge bg-{{ $badgeClass }}">
            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
        </span>
    </span>
</p>
```

#### B. Package Information Display
**Added:** Dedicated alert box for package bookings

**Features:**
- Prominent display with success alert styling
- Shows package name, duration, base price
- Includes package description if available
- Gift icon for visual appeal
- Positioned at top of booking details for visibility

**Implementation:**
```blade
@if($booking->package)
<div class="alert alert-success border-success mb-4">
    <div class="d-flex align-items-start">
        <i class="bi bi-box-seam fs-3 me-3"></i>
        <div class="flex-grow-1">
            <h5 class="alert-heading mb-2">
                <i class="bi bi-gift me-2"></i>Package: {{ $booking->package->pkg_name }}
            </h5>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Duration:</strong> {{ $booking->package->pkg_number_of_nights }} nights</p>
                    <p class="mb-1"><strong>Base Price:</strong> {{ tenant_currency() }} {{ number_format($booking->package->pkg_base_price, 2) }}</p>
                </div>
                <div class="col-md-6">
                    @if($booking->package->pkg_description)
                    <p class="mb-0"><small>{{ strip_tags($booking->package->pkg_description) }}</small></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif
```

#### C. Improved Room Information Display
**Changes:**
- Better formatted arrival/departure dates (e.g., "Monday, January 15, 2025")
- Changed "Is Shared: Yes/No" to "Booking Type: Shared Room / Private Room"
- More professional presentation

**Before:**
```blade
<p><strong>Arrival Date:</strong> {{ \Carbon\Carbon::parse($booking->arrival_date)->format('M d, Y') }}</p>
<p><strong>Departure Date:</strong> {{ \Carbon\Carbon::parse($booking->departure_date)->format('M d, Y') }}</p>
<p><strong>Is Shared:</strong> {{ $booking->is_shared ? 'Yes' : 'No' }}</p>
```

**After:**
```blade
<p><strong>Arrival Date:</strong> {{ \Carbon\Carbon::parse($booking->arrival_date)->format('l, F j, Y') }}</p>
<p><strong>Departure Date:</strong> {{ \Carbon\Carbon::parse($booking->departure_date)->format('l, F j, Y') }}</p>
<p><strong>Booking Type:</strong> {{ $booking->is_shared ? 'Shared Room' : 'Private Room' }}</p>
```

#### D. Enhanced Guest Information Section
**Complete Redesign:**

**Before:** Simple table with limited information
**After:** Detailed card-based layout with:
- Primary guest indicator (👤 icon)
- Additional guests labeled as "👥 Guest 2", "👥 Guest 3", etc.
- Two-column layout for better space utilization
- Special requests displayed in warning alert box
- Adult and children count
- Border separator between multiple guests

**Features:**
- Iterates through `bookingGuests` relationship properly
- Shows full guest details (name, email, phone, nationality)
- Highlights special requests with visual emphasis
- Clean, scannable layout

**Implementation:**
```blade
@foreach($booking->bookingGuests as $index => $bookingGuest)
<div class="mb-3 {{ $loop->last ? '' : 'pb-3 border-bottom' }}">
    <h6 class="text-primary mb-2">
        {{ $bookingGuest->is_primary ? '👤 Primary Guest' : '👥 Guest ' . ($index + 1) }}
    </h6>
    <div class="row">
        <div class="col-md-6">
            <!-- Guest contact info -->
        </div>
        <div class="col-md-6">
            <!-- Guest details (nationality, adults, children) -->
        </div>
    </div>
    @if($bookingGuest->special_requests)
    <div class="alert alert-warning mt-2 mb-0">
        <small><strong>Special Requests:</strong></small><br>
        <small>{{ $bookingGuest->special_requests }}</small>
    </div>
    @endif
</div>
@endforeach
```

#### E. New Action Button
**Added:** Download Booking Info button in sidebar actions card

**Location:** Top of actions card (before check-in/check-out buttons)

**Implementation:**
```blade
<a href="{{ route('tenant.guest-portal.bookings.download', $booking->id) }}" 
   class="btn btn-outline-success" target="_blank">
    <i class="bi bi-download me-2"></i>
    Download Booking Info
</a>
```

**Features:**
- Opens PDF in new tab (target="_blank")
- Outline style to differentiate from primary actions
- Green color to match success theme
- Consistent icon usage (Bootstrap Icons)

### 4. Bookings List View Enhancement

**File:** `resources/views/tenant/guest-portal/bookings.blade.php`

#### Updated Actions Column
**Changed:** Single "View" button to button group with two actions

**Before:**
```blade
<td>
    <a href="{{ route('tenant.guest-portal.bookings.show', $booking->id) }}" 
       class="btn btn-sm btn-outline-primary">
        <i class="bi bi-eye"></i> View
    </a>
</td>
```

**After:**
```blade
<td>
    <div class="btn-group" role="group">
        <a href="{{ route('tenant.guest-portal.bookings.show', $booking->id) }}" 
           class="btn btn-sm btn-outline-primary" title="View Details">
            <i class="bi bi-eye"></i>
        </a>
        <a href="{{ route('tenant.guest-portal.bookings.download', $booking->id) }}" 
           class="btn btn-sm btn-outline-success" 
           target="_blank"
           title="Download Info">
            <i class="bi bi-download"></i>
        </a>
    </div>
</td>
```

**Features:**
- Compact button group saves space
- Icon-only buttons with tooltips
- Download opens in new tab
- Consistent styling with detail view

### 5. PDF Template Reuse

**Existing Template:** `resources/views/tenant/bookings/room-info-pdf.blade.php`

**Why Reuse:**
- Single source of truth for booking PDFs
- Consistent branding across admin and guest portals
- Professional design already tested
- Automatic inheritance of future improvements
- Reduced maintenance overhead

**Template Features:**
- Property logo and information
- Booking status with color-coded badge
- Package information (if applicable)
- Complete booking details (dates, nights, room info)
- Room information with type and rates
- All guest information with special requests
- Property contact information
- Professional footer with generation timestamp

**Data Required:**
```php
compact('booking', 'property', 'currency')
```

**Relationships Loaded:**
```php
'room.type'           // Room type information
'package'             // Package details (if applicable)
'bookingGuests.guest' // All guests with their information
'property'            // Property details
```

## Security Considerations

### Access Control
1. **Guest Authentication Check:**
   - All methods verify guest session via `$this->getGuest()`
   - Redirect to login if not authenticated

2. **Booking Ownership Verification:**
   ```php
   ->whereHas('guests', function($q) use ($guest) {
       $q->where('guest_id', $guest->id);
   })
   ```
   - Ensures guests can only access their own bookings
   - Query-level security prevents unauthorized access

3. **Route Protection:**
   - All routes under `guest.portal` middleware
   - Automatic session verification
   - Redirect to login on failure

### Data Privacy
- Only loads bookings where guest is listed
- No exposure of other guests' bookings
- Secure PDF generation with guest-specific data
- No caching of sensitive information

## User Experience Improvements

### Visual Enhancements
1. **Status Badges:** Color-coded booking statuses for quick recognition
2. **Icon Usage:** Consistent Bootstrap Icons throughout interface
3. **Responsive Layout:** Flexbox with wrapping for mobile devices
4. **Card-Based Design:** Clean, organized information blocks
5. **Alert Boxes:** Prominent display of important information (packages, special requests)

### Functional Improvements
1. **Download Accessibility:** Multiple entry points (header, actions card, list view)
2. **Target Blank:** PDFs open in new tab, preserving current page
3. **Professional PDFs:** Consistent, branded documents suitable for printing
4. **Guest Details:** Comprehensive display of all booking participants
5. **Package Visibility:** Clear indication of package bookings with details

### Information Architecture
1. **Logical Grouping:** Related information grouped in cards
2. **Visual Hierarchy:** Important actions prominently placed
3. **Progressive Disclosure:** Summary in list, details in dedicated view
4. **Contextual Actions:** Relevant actions based on booking status
5. **Breadcrumb Navigation:** Easy return to previous views

## Testing Checklist

### Functionality Testing
- [ ] Guest can view booking list
- [ ] Guest can view individual booking details
- [ ] Download button generates PDF correctly
- [ ] PDF contains all booking information
- [ ] PDF displays package information (if applicable)
- [ ] All guest information appears in PDF
- [ ] Special requests display correctly
- [ ] Status badges show correct colors
- [ ] PDF opens in new tab
- [ ] Filename uses booking code

### Security Testing
- [ ] Unauthenticated users redirected to login
- [ ] Guests cannot download other guests' bookings
- [ ] Direct URL access properly secured
- [ ] Session validation works correctly
- [ ] Unauthorized access returns 404/403

### UI/UX Testing
- [ ] Layout responsive on mobile devices
- [ ] Buttons properly aligned and sized
- [ ] Icons display correctly
- [ ] Status colors appropriate and visible
- [ ] Package alert box displays properly
- [ ] Guest information cards formatted correctly
- [ ] Special requests alerts visible
- [ ] PDF print quality acceptable

### Cross-Browser Testing
- [ ] Chrome: PDF download works
- [ ] Firefox: PDF download works
- [ ] Safari: PDF download works
- [ ] Edge: PDF download works
- [ ] Mobile browsers: Layout responsive

## Files Modified

1. **app/Http/Controllers/Tenant/GuestPortalController.php**
   - Added `downloadBookingInfo()` method
   - Updated `showBooking()` to load correct relationships

2. **routes/tenant.php**
   - Added download route: `tenant.guest-portal.bookings.download`

3. **resources/views/tenant/guest-portal/booking-detail.blade.php**
   - Enhanced header with status badge and download button
   - Added package information alert box
   - Improved room information display
   - Complete redesign of guest information section
   - Added download button in actions card

4. **resources/views/tenant/guest-portal/bookings.blade.php**
   - Enhanced actions column with button group
   - Added download button to list view

## Files Reused (No Changes Needed)

1. **resources/views/tenant/bookings/room-info-pdf.blade.php**
   - Existing PDF template used for guest portal downloads
   - Already contains all necessary styling and structure
   - Professional design with property branding

## Benefits

### For Guests
1. **Convenience:** Easy access to booking information anytime
2. **Documentation:** Professional PDF for records/printing
3. **Clarity:** Clear display of all booking details
4. **Accessibility:** Multiple ways to download information
5. **Transparency:** Complete visibility of booking terms

### For Property Managers
1. **Consistency:** Same PDF format across admin and guest portals
2. **Reduced Support:** Guests self-serve booking information
3. **Professionalism:** Branded, polished documents
4. **Accuracy:** Single source of truth for booking data
5. **Efficiency:** Automated PDF generation

### For System Maintenance
1. **Code Reuse:** Single PDF template for both portals
2. **Easy Updates:** Changes apply to both contexts
3. **Reduced Duplication:** Less code to maintain
4. **Consistent Branding:** Same look and feel everywhere
5. **Bug Prevention:** Fewer places for issues to occur

## Future Enhancements

### Potential Additions
1. **Email PDF:** Send booking info via email
2. **QR Code:** Add QR code to PDF for quick check-in
3. **Itinerary:** Include property amenities and local attractions
4. **Print View:** HTML print-friendly view as alternative to PDF
5. **Multiple Languages:** Support for translated PDFs
6. **Customization:** Allow guests to select what info to include
7. **Share Link:** Generate shareable link to booking details
8. **Calendar Export:** Add to calendar functionality (.ics file)
9. **Mobile App Integration:** Deep link for native app users
10. **Booking Timeline:** Visual timeline of booking lifecycle

### Technical Improvements
1. **Caching:** Cache PDF for repeated downloads
2. **Queue Processing:** Generate PDFs asynchronously for large bookings
3. **Storage:** Option to store PDFs for archival
4. **Watermark:** Add watermark for verification/authenticity
5. **Compression:** Optimize PDF file size
6. **Analytics:** Track download frequency and patterns
7. **Version Control:** Track PDF generation history
8. **Batch Download:** Download multiple bookings at once

## Related Documentation

- `GUEST_PORTAL_IMPLEMENTATION.md` - Complete guest portal overview
- `GUEST_PORTAL_NAVIGATION_UPDATE.md` - Navigation system documentation
- `GUEST_PORTAL_INVOICE_INTEGRATION.md` - Invoice system integration
- Tenant booking management documentation
- PDF generation best practices

## Notes

### Design Decisions
1. **Reuse over Recreation:** Deliberately chose to reuse tenant PDF template rather than create separate guest version
2. **Icon Consistency:** Used Bootstrap Icons throughout for cohesive design
3. **Color Coding:** Status colors match admin portal for consistency
4. **Target Blank:** PDFs open in new tab to preserve navigation state
5. **Button Groups:** Compact action buttons to save space in list view

### Technical Considerations
1. **Eager Loading:** Proper relationship loading prevents N+1 queries
2. **Security First:** Query-level security ensures no unauthorized access
3. **Error Handling:** 404 for non-existent bookings, 403 for unauthorized
4. **PDF Library:** Uses existing DomPDF installation
5. **Helper Functions:** Leverages `current_property()` and `property_currency()` helpers

### Known Limitations
1. **PDF Size:** Large bookings with many guests may produce large PDFs
2. **Generation Time:** PDF generation blocks request (synchronous)
3. **Logo Display:** Requires correct logo path in assets
4. **Mobile Printing:** Some mobile browsers have limited print support
5. **Special Characters:** Non-Latin characters may need font configuration

## Maintenance

### Regular Tasks
- Monitor PDF generation performance
- Review guest feedback on document clarity
- Update styling to match branding changes
- Test after DomPDF library updates
- Verify logo and asset paths remain valid

### When to Update
- Booking structure changes
- New fields added to bookings
- Package features modified
- Guest information requirements change
- Branding/logo updates
