$(function() {
  let currentStep = 1;
  let maxGuests = 1; // Will be set when room selected
  
  function showStep(step) {
    $('#bookingStepper .nav-link').removeClass('active');
    $('#bookingStepper .tab-pane').removeClass('show active');
    $('#bookingStepper .nav-link').eq(step-1).addClass('active');
    $('#bookingStepper .tab-pane').eq(step-1).addClass('show active');
    currentStep = step;
  }
  
  $('#toStep2').click(function() { showStep(2); updateSummary(); });
  $('#toStep3').click(function() { showStep(3); updateSummary(); });
  $('#toStep4').click(function() { showStep(4); updateSummary(); showConfirmation(); });
  $('#backToStep1').click(function() { showStep(1); });
  $('#backToStep2').click(function() { showStep(2); });
  $('#backToStep3').click(function() { showStep(3); });
  
  // Room selection: update rate, guest capacity
  $('#room_id, #is_shared').change(function() {
    let roomOpt = $('#room_id option:selected');
    let isShared = $('#is_shared').val() == '1';
    let rate = isShared ? roomOpt.data('shared-rate') : roomOpt.data('rate');
    maxGuests = isShared ? roomOpt.data('max') : 1;
    $('#daily_rate').val(rate);
    // Calculate total
    let nights = getNights();
    $('#total_amount').val(rate * nights * maxGuests);
    updateSummary();
    // Reset guest fields if maxGuests changed
    if ($('#additionalGuestFields').children().length > (maxGuests-1)) {
      $('#additionalGuestFields').empty();
    }
  });
  
  // Add guest fields
  $('#addGuestBtn').click(function() {
    let count = $('#additionalGuestFields').children().length + 2; // 1-based index
    if (count <= maxGuests) {
      let field = `
            <div class="border p-2 mb-2">
                <h6>Guest ${count}</h6>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="guest_name_${count}">Name</label>
                        <input type="text" class="form-control" id="guest_name_${count}" name="guests[${count-1}][name]" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="guest_email_${count}">Email</label>
                        <input type="email" class="form-control" id="guest_email_${count}" name="guests[${count-1}][email]" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="guest_phone_${count}">Phone</label>
                        <input type="text" class="form-control" id="guest_phone_${count}" name="guests[${count-1}][phone]">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="special_requests_${count}">Special Requests</label>
                        <input type="text" class="form-control" id="special_requests_${count}" name="guests[${count-1}][special_requests]">
                    </div>
                </div>
            </div>`;
      $('#additionalGuestFields').append(field);
    }
  });
  
  // Date selection: validate and update summary
  $('#arrival_date, #departure_date').change(function() {
    updateSummary();
    let arrival = $('#arrival_date').val();
    $('#departure_date').attr('min', arrival);
  });
  
  // Stepper summary
  function updateSummary() {
    $('#summaryDates').text($('#arrival_date').val() + ' to ' + $('#departure_date').val());
    let roomText = $('#room_id option:selected').text();
    $('#summaryRoom').text(roomText);
    let guests = $('#guestFields input[type="text"][id^="guest_name_"]').map(function(){ return $(this).val(); }).get().join(', ');
    $('#summaryGuests').text(guests || 'Not entered');
    $('#summaryRate').text($('#daily_rate').val());
    $('#summaryTotal').text($('#total_amount').val());
    let reqs = $('#guestFields input[type="text"][id^="special_requests_"]').map(function(){ return $(this).val(); }).get().join('; ');
    $('#summaryRequests').text(reqs);
  }
  
  // Confirmation summary
  function showConfirmation() {
    let html = `
        <ul class="list-group">
            <li class="list-group-item"><strong>Date Range:</strong> ${$('#summaryDates').text()}</li>
            <li class="list-group-item"><strong>Room:</strong> ${$('#summaryRoom').text()}</li>
            <li class="list-group-item"><strong>Guests:</strong> ${$('#summaryGuests').text()}</li>
            <li class="list-group-item"><strong>Rate:</strong> ${$('#summaryRate').text()}</li>
            <li class="list-group-item"><strong>Total:</strong> ${$('#summaryTotal').text()}</li>
            <li class="list-group-item"><strong>Requests:</strong> ${$('#summaryRequests').text()}</li>
        </ul>`;
    $('#confirmationSummary').html(html);
  }
  
  function getNights() {
    let a = $('#arrival_date').val();
    let d = $('#departure_date').val();
    if(a && d) {
      let start = new Date(a);
      let end = new Date(d);
      let diff = (end - start)/(1000*60*60*24);
      return diff > 0 ? diff : 1;
    }
    return 1;
  }
  
  // Initial step
  showStep(1);
});