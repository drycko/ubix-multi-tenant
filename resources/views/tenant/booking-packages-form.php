<?php
/**
 * Booking Form: Three-Step Wizard
 * this is a user form to book from packages we offer
 */
if (!defined('ABSPATH')) exit;

// $booking_date = date('Y-m-d');
$checkInDate = date('Y-m-d H:i:s', strtotime($booking_date));
$checkOutDate = date('Y-m-d H:i:s', strtotime($booking_date_end));

// $booking_range = date('d/m/Y', strtotime($booking_date))." — ".date('d/m/Y', strtotime($booking_date_end));
$booking_range = "Select range";
// $booking_days = uku_time_difference($checkInDate, $checkOutDate, 86400);
$booking_days = '';

$booking_shared = 'Shared: <span class="text-danger">No</span>';
$booking_room = '<span>Select Room</span>';
$booking_cost = 'R<span>0.00</span>';

$booking_package = 'Choose a package';

$ubix_use_divi = false;

$ubix_icons_path = UBIX_PLUGIN_ASSETS_URL.'/icons';

$date_now_today = date('Y-m-d H:i:s');

?>
<div class="ubix-content-wrapper bg-transparent mb-4">
  <div class="w-100">
    <div class="text-center">
      <h2 class="ubix-content-title mb-0">Book Your Package</h2>
      <p class="ubix-content-subtitle mb-4">Follow the steps to complete your booking</p>
    </div>
    <div class="ubix-booking-wizard">
      <div class="ubix-progressbar">
        <div class="ubix-step active" data-step="1">
          <!-- <span class="ubix-step-number">1</span><br> -->
          <span class="ubix-step-icon"><img class="ubix-icon" src="<?= $ubix_icons_path ?>/brook-package.png" alt="Package"></span>
          <span class="ubix-step-label">Package</span>
          <span><strong class="txt-ubix-blue" id="selectedPackageName"></strong></span>
        </div>
        <div class="ubix-step" data-step="2">
          <!-- <span class="ubix-step-number">2</span><br> -->
          <span class="ubix-step-icon"><img class="ubix-icon" src="<?= $ubix_icons_path ?>/brook-date.png" alt="Package"></span>
          <span class="ubix-step-label">Dates</span>
          <span><strong class="txt-ubix-blue" id="selectedCheckInDate"></strong> - <strong class="txt-ubix-blue" id="selectedCheckOutDate"></strong></span>
        </div>
        <div class="ubix-step" data-step="3">
          <!-- <span class="ubix-step-number">3</span><br> -->
          <span class="ubix-step-icon"><img class="ubix-icon" src="<?= $ubix_icons_path ?>/brook-rooms.png" alt="Package"></span>
          <span class="ubix-step-label">Rooms</span>
          <span><strong class="txt-ubix-blue" id="selectedRoomType"></strong></span>
        </div>
        <div class="ubix-step" data-step="4">
          <!-- <span class="ubix-step-number">4</span><br> -->
          <span class="ubix-step-icon"><img class="ubix-icon" src="<?= $ubix_icons_path ?>/brook-cost.png" alt="Package"></span>
          <span class="ubix-step-label">Cost</span>
          <span><strong class="txt-ubix-blue" id="ubixBookingCost"></strong></span>
        </div>
      </div>

      <form id="ubixPackageBookingForm" class="ubix-form-card" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
        <!-- form messages -->
        <div hidden id="ubixFormMessages" class="mb-3"></div>
        
        <?php wp_nonce_field('save_booking_nonce'); ?>
        <input type="hidden" name="action" value="save_booking">

        <div class="ubix-step-panel" data-step="1">
          <!-- <h3 class="ubix-header">Choose a Package</h3> -->
          <p class="txt-brook-blue hl5">Choose a Package</p>
          <small hidden class="ubix-note"><strong class="text-danger">NB:</strong> Please select a package to proceed.</small>
          <div id="packageSelection" class="ubix-card-list row">
            <!-- Package options here -->
          </div>
          <div hidden id="packageSelectionMessage" class="ubix-selection-message"></div>
        </div>
        <div class="ubix-step-panel" data-step="2" style="display:none;">
          <!-- <h3 class="ubix-header">Select Dates</h3> -->
          <p class="txt-brook-blue hl5">Select Dates</p>
          <small class="ubix-note"><strong class="text-danger">NB:</strong> A minimum of <b>2 nights</b> and a maximum of <b>6 nights</b> are allowed per booking.</small>
          <!-- calendar -->
          <div class="row mb-3">
            <div class="col-md-6">
              <!-- <label class="form-label">Calendar</label> -->
              <div class="mb-1 row" style="margin-left: 4px;">
                <!-- label-round are not showing, CSS to make them show even with no children  -->
                <div class="col-md-3 col-3"><small style="font-size: 9px;"><span class="label-round today-date"></span> Current Day</small></div>
                <div class="col-md-3 col-3"><small style="font-size: 9px;"><span class="label-round booked-date"></span> Non-CheckIn</small></div>
                <div class="col-md-3 col-3"><small style="font-size: 9px;"><span class="label-round available-date"></span> Available</small></div>
                <div class="col-md-3 col-3"><small style="font-size: 9px;"><span class="label-round selected-date"></span> Selected</small></div>
              </div>
              <hr class="sm">
              <div class="calendar-wrapper">
                <div id="calendar" class="calendar"></div>
              </div>
            </div>
            <div id="datePicker" class="col-md-6">
              <div class="form-group mb-3">
                <label class="form-label">Booking Date Range <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-lg" id="bookingDateRange" name="bookingDateRange" value="<?php echo esc_attr($booking_range); ?>" placeholder="Select Date Range" readonly required>
              </div>
              <div hidden class="form-group mb-3">
                <label class="form-label">Check-In Date <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-lg" id="checkInDate" name="checkInDate" value="<?php echo esc_attr($booking_date); ?>" placeholder="Select Check-In Date" readonly required>
              </div>
              <div hidden class="form-group mb-3">
                <label class="form-label">Check-Out Date <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-lg" id="checkOutDate" name="checkOutDate" value="<?php echo esc_attr($booking_date_end); ?>" placeholder="Select Check-Out Date" readonly required>
              </div>
              <!-- shared room check switch -->
              <div class="form-group mb-3">
                <label class="form-label">Shared Room?</label><br>
                <div class="ubix-radio-group d-flex gap-3 align-items-center mt-2 mb-2">
                  <input type="radio" class="ubix-radio" name="sharedRoomOption" value="yes" id="sharedRoomYes">
                  <label class="ubix-radio-label" for="sharedRoomYes">Yes</label>
                  <input type="radio" class="ubix-radio" checked name="sharedRoomOption" value="no" id="sharedRoomNo">
                  <label class="ubix-radio-label" for="sharedRoomNo">No</label>
                </div>
                <div class="form-text"><small class="text-muted">Selecting 'Yes' may pair you with another guest in a shared room.</small></div>
              </div>
              <div class="mb-3 text-center">
                <button disabled type="button" class="btn btn-primary" id="checkAvailabilityBtn">CHECK AVAILABILITY</button>
              </div>
              <div id="dateSelectionMessage" class="ubix-selection-message"></div>
            </div>
          </div>
          <!-- Date picker and info here -->
          <!-- <div class="row mb-3">
            
          </div> -->
        </div>
        <div class="ubix-step-panel" data-step="3" style="display:none;">
          <!-- <h3 class="ubix-header">Select Room</h3> -->
          <p class="txt-brook-blue hl5">Select Room</p>
          <div id="roomSelection" class="ubix-card-list">
            <!-- Room options here -->
          </div>
        </div>
        <div class="ubix-step-panel" data-step="4" style="display:none;">
          <!-- <h3 class="ubix-header">Guest Details</h3> -->
          <!-- Guest form fields here -->
          <!-- <hr> -->
          <p class="txt-brook-blue hl5">Primary Guest</p>
          <div id="guestDetails">
            <!-- Guest details fields here -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label" for="guestFirstName">First Name <span class="text-danger">*</span></label>
                <!-- I need css to add padding on the left of all placeholder text and input text and change the size of the placeholder and input text -->
                <input type="text" class="form-control form-control-lg" name="guestFirstName" id="guestFirstName" placeholder="Enter here" required>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="guestLastName">Surname <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-lg" name="guestLastName" id="guestLastName" placeholder="Enter here" required>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label" for="guestPhone">Phone Number <span class="text-danger">*</span></label>
                <input type="text" onkeypress="return NumAvoidSpace(event)" class="form-control form-control-lg" name="guestPhone" id="guestPhone" placeholder="Enter here" required>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="guestEmail">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control form-control-lg" name="guestEmail" id="guestEmail" placeholder="Enter here" required>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-4">
                <label class="form-label" for="guestIdNo">ID/Passport Number <span class="text-danger">*</span></label>
                <input type="text" onkeypress="return AvoidSpace(event)" class="form-control form-control-lg" id="guestIdNo" name="guestIdNo" placeholder="Enter here" required>
                <div id="guest-id-message" hidden></div>
              </div>
              <div class="col-md-4">
                <label for="guestGownSize" class="form-label">Gown Size <span class="text-danger">*</span></label>
                <select type="text" name="guestGownSize" id="guestGownSize" class="form-control form-control-lg" >
                  <option value="S">Small</option>
                  <option value="M">Medium</option>
                  <option value="L">Large</option>
                  <option value="XL">XL</option>
                  <option value="XXL">XXL</option>
                  <option value="3XL">3XL</option>
                  <option value="4XL">4XL</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Returning Guest?</label><br>
                <div class="form-check form-check-inline">
                  <input type="radio" class="form-check-input" name="guestReturning" placeholder="Enter here" value="yes" id="returningYes">
                  <label class="form-check-label" for="returningYes">Yes</label>
                </div>
                <div class="form-check form-check-inline">
                  <input type="radio" class="form-check-input" name="guestReturning" placeholder="Enter here" value="no" id="returningNo" checked>
                  <label class="form-check-label" for="returningNo">No</label>
                </div>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Any Dietary Requirements or Food Allergies?</label>
                <textarea type="text" class="form-control form-control-lg" name="dietFoodAllergies" rows="3" placeholder="Enter here" ></textarea>
              </div>

              <div class="col-md-6">
                <label class="form-label">Special Requests or Information?</label>
                <textarea type="text" class="form-control form-control-lg" name="specialRequests" rows="3" placeholder="Enter here" ></textarea>
              </div>
            </div>
          
          </div>

          <!-- Duplicate if Shared Room -->
          <div id="sharedRoomGuest2" hidden>
            <p class="txt-brook-blue hl5">Second Guest</p>
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label" for="additionalGuestFirstName">First Name <span class="text-danger">*</span></label>
                <!-- I need css to add padding on the left of all placeholder text and input text and change the size of the placeholder and input text -->
                <input type="text" class="form-control form-control-lg" name="additionalGuestFirstName" id="additionalGuestFirstName" placeholder="Enter here" >
              </div>
              <div class="col-md-6">
                <label class="form-label" for="additionalGuestLastName">Surname <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-lg" name="additionalGuestLastName" id="additionalGuestLastName" placeholder="Enter here" >
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label" for="additionalGuestPhone">Phone Number <span class="text-danger">*</span></label>
                <input type="text" onkeypress="return NumAvoidSpace(event)" class="form-control form-control-lg" name="additionalGuestPhone" id="additionalGuestPhone" placeholder="Enter here" >
              </div>
              <div class="col-md-6">
                <label class="form-label" for="additionalGuestEmail">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control form-control-lg" name="additionalGuestEmail" id="additionalGuestEmail" placeholder="Enter here" >
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-4">
                <label class="form-label" for="additionalGuestIdNo">ID/Passport Number <span class="text-danger">*</span></label>
                <input type="text" onkeypress="return AvoidSpace(event)" class="form-control form-control-lg" id="additionalGuestIdNo" name="additionalGuestIdNo" placeholder="Enter here" >
                <div id="additionalGuest-id-message" hidden></div>
              </div>
              <div class="col-md-4">
                <label for="guestGownSize" class="form-label">Gown Size <span class="text-danger">*</span></label>
                <select type="text" name="guestGownSize" id="guestGownSize" class="form-control form-control-lg" >
                  <option value="S">Small</option>
                  <option value="M">Medium</option>
                  <option value="L">Large</option>
                  <option value="XL">XL</option>
                  <option value="XXL">XXL</option>
                  <option value="3XL">3XL</option>
                  <option value="4XL">4XL</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Returning Guest?</label><br>
                <div class="form-check form-check-inline">
                  <input type="radio" class="form-check-input" name="additionalGuestReturning" placeholder="Enter here" value="yes" id="additionalReturningYes">
                  <label class="form-check-label" for="additionalReturningYes">Yes</label>
                </div>
                <div class="form-check form-check-inline">
                  <input type="radio" class="form-check-input" name="additionalGuestReturning" placeholder="Enter here" value="no" id="additionalReturningNo" checked>
                  <label class="form-check-label" for="additionalReturningNo">No</label>
                </div>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Any Dietary Requirements or Food Allergies?</label>
                <textarea type="text" class="form-control form-control-lg" name="additionalDietFoodAllergies" rows="3" placeholder="Enter here" ></textarea>
              </div>

              <div class="col-md-6">
                <label class="form-label">Additional Requests or Information?</label>
                <textarea type="text" class="form-control form-control-lg" name="additionalSpecialRequests" rows="3" placeholder="Enter here" ></textarea>
              </div>
            </div>
            
          </div>
          <div hidden>
            <div class="mb-3">
              <input type="text" class="form-control" id="pickedPackageId" name="pickedPackageId"> 
              <input type="text" class="form-control" id="bookingPackageSelected" name="bookingPackageSelected"> 
              <input type="text" class="form-control" id="selectedPackageNights" name="selectedPackageNights"> 
              <input type="text" class="form-control" id="pickedRoomTypeId" name="pickedRoomTypeId"> 
              <input type="text" class="form-control" id="pickedRoomId" name="pickedRoomId"> 
              <input type="text" class="form-control" id="bookingRoomSelected" name="bookingRoomSelected"> <!-- room Type guest selected -->
              <input type="text" class="form-control" id="bookingRate" name="bookingRate"> <!-- per night including both guests if shared -->
            </div>
          </div>
          
        </div>
        <!-- this is supposed to be sticky -->
        <div class="ubix-wizard-nav mt-3 sticky-bottom bg-white py-3">
          <button type="button" class="ubix-btn prevStep" id="prevStep" disabled>Back</button>
          <button type="button" class="ubix-btn nextStep" id="nextStep">Next</button>
          <button type="submit" class="ubix-btn" id="submitBooking" style="display:none;">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>
