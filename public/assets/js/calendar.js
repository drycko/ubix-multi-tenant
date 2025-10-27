$(function() {
  "use strict";
  // ✅ **Generate Available Date Ranges Based on Allowed Check-In Days**
  function generateAvailableDateRanges(allowedDays, nights) {
    // allowedDays should be weekday names, e.g. ['MON','WED','FRI']
    const today = new Date();
    const currentMonth = today.getMonth();
    const currentYear = today.getFullYear();

    console.log('generateAvailableDateRanges called')

    // Generate the calendar for the current month
    generateCalendar(currentMonth, currentYear, allowedDays, nights);
  }
  // only if this is booking create page
  if (typeof allowedWeekdays !== 'undefined' && typeof packageNights !== 'undefined') {
    generateAvailableDateRanges(allowedWeekdays, packageNights);
  }

  // Function to generate the calendar
  function generateCalendar(month, year, allowedDays, nights) {
    // allowedDays should be weekday names, e.g. ['MON','WED','FRI']
    $('#calendar').empty();

    console.log('generateCalendar called')

    // Generate the calendar header with Next and Previous Month Buttons
    $('#calendar').prepend(`
      <div class="calendar-header d-flex justify-content-between align-items-center">
        <button class="btn btn-sm btn-outline-success prev-month month-nav-btn">
          &lt; Previous
        </button>
        <h4 class="month-year text-center">
          ${new Date(year, month).toLocaleString('default', { month: 'long' })} ${year}
        </h4>
        <button class="btn btn-outline-success btn-sm next-month month-nav-btn">
          Next &gt;
        </button>
      </div>
    `);

    // Add click event listeners for the Next and Previous buttons
    $('.prev-month').on('click', function () {
      const prevMonth = month === 0 ? 11 : month - 1;
      const prevYear = month === 0 ? year - 1 : year;
      generateCalendar(prevMonth, prevYear, allowedDays, nights);
    });

    $('.next-month').on('click', function () {
      const nextMonth = month === 11 ? 0 : month + 1;
      const nextYear = month === 11 ? year + 1 : year;
      generateCalendar(nextMonth, nextYear, allowedDays, nights);
    });

    // Add weekday headers (Sun - Sat)
    const weekdays = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
    const weekdayRow = weekdays.map(day => `<div class="week-day">${day}</div>`).join('');
    $('#calendar').append(`<div class="weekdays">${weekdayRow}</div>`);

    const firstDay = getFirstDayOfMonth(month, year);
    const daysInMonth = getDaysInMonth(month, year);
    const today = new Date();
    const todayDate = today.getDate();
    const currentMonth = today.getMonth();
    const currentYear = today.getFullYear();

    let calendarGrid = '';

    // Loop to generate empty cells before the first day
    for (let i = 0; i < firstDay; i++) {
      calendarGrid += '<div class="empty-cell"></div>';
    }

    // Loop to generate each day in the calendar
    for (let day = 1; day <= daysInMonth; day++) {
      const date = new Date(year, month, day);
      // Get weekday as 'MON', 'TUE', ...
      const checkinWeekday = date.toLocaleDateString('en-US', { weekday: 'short' }).toUpperCase();
      const isAllowed = allowedDays.includes(checkinWeekday);
      const isPast = date < today;
      const isToday = day === todayDate && month === currentMonth && year === currentYear;
      const dayClass = isPast ? 'past-date' : !isAllowed ? 'unavailable-date' : 'available-date';
      const todayClass = isToday ? 'today-date' : '';

      calendarGrid += `
        <div class="date ${dayClass} ${todayClass} p-2" data-date="${year}-${month + 1}-${day}">
          <p class="h5">${day}</p>
          <!-- ${!isAllowed ? '<div class="event-name badge bg-danger">NO</div>' : ''} -->
        </div>
      `;
    }

    // Append the generated grid of days into the calendar
    $('#calendar').append(`<div class="calendar-grid">${calendarGrid}</div>`);
  }

  // ✅ Handle date selection from the calendar
  $(document).on('click', '.date.available-date', function () {
    const $date = $(this);
    const selectedDateStr = $date.data('date');
    const selectedDate = new Date(selectedDateStr);
    const checkoutDate = new Date(selectedDateStr);
    checkoutDate.setDate(checkoutDate.getDate() + packageNights);

    const formattedCheckin = selectedDate.toLocaleDateString('en-ZA');
    const formattedCheckout = checkoutDate.toLocaleDateString('en-ZA');
    const dateRange = `${formattedCheckin} - ${formattedCheckout}`;

    // Clear all previous selections by restoring original classes
    restoreOriginalDateClasses();

    // Set the new selected date
    $date.removeClass('available-date').addClass('selected-date');
    
    // Set the checkout date if it exists in the calendar
    const checkoutDateElement = $(`.date[data-date='${checkoutDate.getFullYear()}-${checkoutDate.getMonth() + 1}-${checkoutDate.getDate()}']`);
    if (checkoutDateElement.length) {
      checkoutDateElement.removeClass('available-date unavailable-date past-date').addClass('selected-checkout-date');
    }
    
    // Set the dates in between as range dates
    $('.date').each(function() {
      const dateStr = $(this).data('date');
      const date = new Date(dateStr);
      if (date > selectedDate && date < checkoutDate) {
        $(this).removeClass('available-date unavailable-date past-date').addClass('other-in-range');
      }
    });

    // Update UI and hidden fields
    $('#reservation').val(dateRange);
    $('.ubook-booking-range').html(`<strong class="txt-brook-blue">${dateRange}</strong>`);
    $('#arrival_date').val(selectedDate.toISOString().split('T')[0]); // format to yyyy-mm-dd
    $('#departure_date').val(checkoutDate.toISOString().split('T')[0]); // format to yyyy-mm-dd

  });

  // ✅ Function to restore original date classes
  function restoreOriginalDateClasses() {
    $('.date').each(function() {
      const $dateElement = $(this);
      const dateStr = $dateElement.data('date');
      const date = new Date(dateStr);
      const today = new Date();
      
      // Remove all selection-related classes
      $dateElement.removeClass('selected-date selected-checkout-date other-in-range');
      
      // Restore original class based on date properties
      const checkinWeekday = date.toLocaleDateString('en-US', { weekday: 'short' }).toUpperCase();
      const isAllowed = allowedWeekdays.includes(checkinWeekday);
      const isPast = date < today;
      
      if (isPast) {
        $dateElement.removeClass('available-date unavailable-date').addClass('past-date');
      } else if (!isAllowed) {
        $dateElement.removeClass('available-date past-date').addClass('unavailable-date');
      } else {
        $dateElement.removeClass('past-date unavailable-date').addClass('available-date');
      }
    });
  }


  // ✅ **helper functions for calendar generation**
  function getFirstDayOfMonth(month, year) {
    return new Date(year, month, 1).getDay();
  }

  function getDaysInMonth(month, year) {
    return new Date(year, month + 1, 0).getDate();
  }
});