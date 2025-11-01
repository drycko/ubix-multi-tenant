$(function() {
  "use strict";

  // Allowed weekdays, e.g. ['MON','WED','FRI'] or leave empty for any
  var allowedWeekdays = typeof window.allowedWeekdays !== 'undefined' ? window.allowedWeekdays : [];
  var calendarTargetArrival = '#arrival_date';
  var calendarTargetDeparture = '#departure_date';

  // Initialize with today’s month
  var today = new Date();
  var currentMonth = today.getMonth();
  var currentYear = today.getFullYear();

  generateCalendar(currentMonth, currentYear, allowedWeekdays);

  // Calendar navigation
  $(document).on('click', '.prev-month', function () {
    let month = parseInt($('#calendar').data('month'));
    let year = parseInt($('#calendar').data('year'));
    const prevMonth = month === 0 ? 11 : month - 1;
    const prevYear = month === 0 ? year - 1 : year;
    generateCalendar(prevMonth, prevYear, allowedWeekdays);
  });

  $(document).on('click', '.next-month', function () {
    let month = parseInt($('#calendar').data('month'));
    let year = parseInt($('#calendar').data('year'));
    const nextMonth = month === 11 ? 0 : month + 1;
    const nextYear = month === 11 ? year + 1 : year;
    generateCalendar(nextMonth, nextYear, allowedWeekdays);
  });

  // Calendar generator
  function generateCalendar(month, year, allowedDays) {
    $('#calendar').empty().data('month', month).data('year', year);

    $('#calendar').prepend(`
      <div class="calendar-header d-flex justify-content-between align-items-center">
        <button class="btn btn-sm btn-outline-success prev-month month-nav-btn">&lt; Previous</button>
        <h4 class="month-year text-center">
          ${new Date(year, month).toLocaleString('default', { month: 'long' })} ${year}
        </h4>
        <button class="btn btn-outline-success btn-sm next-month month-nav-btn">Next &gt;</button>
      </div>
    `);

    // Weekday headers
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

    // Empty cells before first day
    for (let i = 0; i < firstDay; i++) {
      calendarGrid += '<div class="empty-cell"></div>';
    }

    // Days loop
    for (let day = 1; day <= daysInMonth; day++) {
      const date = new Date(year, month, day);
      const checkinWeekday = date.toLocaleDateString('en-US', { weekday: 'short' }).toUpperCase();
      const isAllowed = allowedDays.length ? allowedDays.includes(checkinWeekday) : true;
      const isPast = date < today.setHours(0,0,0,0);
      const isToday = day === todayDate && month === currentMonth && year === currentYear;
      const dayClass = isPast ? 'past-date' : !isAllowed ? 'unavailable-date' : 'available-date';
      const todayClass = isToday ? 'today-date' : '';

      calendarGrid += `
        <div class="date ${dayClass} ${todayClass} p-2" data-date="${year}-${month + 1}-${day}">
          <p class="h5">${day}</p>
        </div>
      `;
    }
    $('#calendar').append(`<div class="calendar-grid">${calendarGrid}</div>`);
  }

  // Step 1: Select Arrival
  var arrivalDate = null;
  var departureDate = null;

  $(document).on('click', '.date.available-date', function () {
    // If arrival not yet selected, select this as arrival
    if (!arrivalDate) {
      restoreOriginalDateClasses();
      $(this).removeClass('available-date').addClass('selected-date');
      arrivalDate = parseDateString($(this).data('date'));
      $(calendarTargetArrival).val(formatDateInput(arrivalDate));
      $(calendarTargetDeparture).val('');
      departureDate = null;
      $('.date.available-date').addClass('can-select-departure');
      // update booking-range-preview (why is it when I select the arrival date first, it gives the day before that?)
      $('.booking-range-preview').html(`<p class="mb-0">Selected Check-In: <strong>${formatDateInput(arrivalDate)}</strong></p>`);
      
    }
  });

  // Step 2: Select Departure
  $(document).on('click', '.date.can-select-departure', function () {
    if (arrivalDate) {
      let selected = parseDateString($(this).data('date'));
      if (selected > arrivalDate) {
        departureDate = selected;
        $(this).removeClass('available-date can-select-departure').addClass('selected-checkout-date');
        $(calendarTargetDeparture).val(formatDateInput(departureDate));

        // Highlight range
        $('.date').each(function() {
          let dateStr = $(this).data('date');
          let date = parseDateString(dateStr);
          if (date > arrivalDate && date < departureDate) {
            $(this).addClass('other-in-range');
          }
        });
        // Remove departure selection ability
        $('.date.can-select-departure').removeClass('can-select-departure');
        // update booking-range-preview (also fix the issue with arrival date showing one day before)
        $('.booking-range-preview').html(`
          <p class="mb-0">
            Selected Dates: 
            <strong>${formatDateInput(arrivalDate)} - ${formatDateInput(departureDate)}</strong>
          </p>
        `);
      }
    }
  });

  // Reset on clicking arrival again
  $(document).on('click', '.selected-date', function() {
    restoreOriginalDateClasses();
    arrivalDate = null;
    departureDate = null;
    $(calendarTargetArrival).val('');
    $(calendarTargetDeparture).val('');
  });

  // ✅ Confirm button to close modal (additional actions can be added here)
  $('#confirmCalendarSelection').on('click', function() {
    $('#calendarModal').modal('hide');
  });

  // ✅ Reset button to clear selections
  $('#resetCalendarSelection').on('click', function() {
    restoreOriginalDateClasses();
    arrivalDate = null;
    departureDate = null;
    $(calendarTargetArrival).val('');
    $(calendarTargetDeparture).val('');
  });

  // Helper functions
  function restoreOriginalDateClasses() {
    $('.date').removeClass('selected-date selected-checkout-date other-in-range can-select-departure');
    $('.date').each(function() {
      const $dateElement = $(this);
      const dateStr = $dateElement.data('date');
      const date = parseDateString(dateStr);
      const today = new Date();
      const checkinWeekday = date.toLocaleDateString('en-US', { weekday: 'short' }).toUpperCase();
      const isAllowed = allowedWeekdays.length ? allowedWeekdays.includes(checkinWeekday) : true;
      const isPast = date < today.setHours(0,0,0,0);

      $dateElement.removeClass('available-date unavailable-date past-date');
      if (isPast) {
        $dateElement.addClass('past-date');
      } else if (!isAllowed) {
        $dateElement.addClass('unavailable-date');
      } else {
        $dateElement.addClass('available-date');
      }
    });
  }

  function parseDateString(str) {
    // 'YYYY-M-D'
    const parts = str.split('-');
    return new Date(parts[0], parts[1] - 1, parts[2]);
  }

  function formatDateInput(d) {
    if (!d) return '';
    // Use local date values, pad with zero if needed
    let year = d.getFullYear();
    let month = String(d.getMonth() + 1).padStart(2, '0');
    let day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  // function formatDateInput(d) {
  //   if (!d) return '';
  //   return d.toISOString().split('T')[0];
  // }

  // Calendar helpers
  function getFirstDayOfMonth(month, year) {
    return new Date(year, month, 1).getDay();
  }

  function getDaysInMonth(month, year) {
    return new Date(year, month + 1, 0).getDate();
  }

});