import "./libs/trix";
// Import jQuery first - this is crucial!
import $ from 'jquery';
window.$ = window.JQuery = $;

// Import Select2 and manually register it with jQuery
import Select2 from 'select2';
Select2($);

import './bootstrap';

import * as bootstrap from 'bootstrap';

// Make bootstrap available globally
window.bootstrap = bootstrap;

// import bootstrap icons
import 'bootstrap-icons/font/bootstrap-icons.css';

// Import FontAwesome CSS
import '@fortawesome/fontawesome-free/css/all.css';

// Import Summernote
import 'summernote/dist/summernote-lite.min.css'; // Lite version CSS
import 'summernote'; // Summernote JS

// Import Select2 CSS
import 'select2/dist/css/select2.css';

// import my calendar js from public assets only for booking pages

import '../../public/assets/js/calendar.js';

// import my roles js from public assets only for roles pages
if (window.location.pathname.includes('/roles')) {
    import('../../public/assets/js/roles.js');
}

// import my permissions js from public assets only for permissions pages
if (window.location.pathname.includes('/permissions')) {
    import('../../public/assets/js/permissions.js');
}

// import my room-status js from public assets only for room-status pages
if (window.location.pathname.includes('/room-status')) {
    import('../../public/assets/js/room-status.js');
}

// import my housekeeping js from public assets only for housekeeping pages
if (window.location.pathname.includes('/housekeeping')) {
    import('../../public/assets/js/housekeeping.js');
}

// import my cleaning-schedule js from public assets only for cleaning-schedule pages
if (window.location.pathname.includes('/cleaning-schedule')) {
    import('../../public/assets/js/cleaning-schedule.js');
}

// import my maintenance js from public assets only for maintenance pages
if (window.location.pathname.includes('/maintenance')) {
    import('../../public/assets/js/maintenance.js');
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Check if libraries are available
    console.log('jQuery version:', $.fn.jquery);
    console.log('Select2 available:', typeof $.fn.select2 === 'function');
    console.log('Summernote available:', typeof $.fn.summernote === 'function');

    // Enable Bootstrap tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Enable Bootstrap popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));

    // initialize select2 for single selects
    const initSingleSelect2 = () => {
        console.log('Initializing single Select2...');
        const singleSelects = document.querySelectorAll('.select2-single');
        singleSelects.forEach(select => {
            if (typeof $.fn.select2 === 'function') {
                $(select).select2({
                    placeholder: "Select an option",
                    allowClear: true,
                    width: '100%'
                });
                console.log('Single Select2 initialized for', select.id || select.className);
            } else {
                console.error('Select2 is not available for', select);
            }
        });
    };
    initSingleSelect2();

    // Initialize Select2 for multi-selects
    const initSelect2 = () => {
        console.log('Initializing Select2...');
        const multiSelects = document.querySelectorAll('.select2-multi');
        multiSelects.forEach(select => {
            if (typeof $.fn.select2 === 'function') {
                $(select).select2({
                    placeholder: "Select options",
                    allowClear: true,
                    closeOnSelect: false,
                    width: '100%'
                });
                console.log('Select2 initialized for', select.id || select.className);
            } else {
                console.error('Select2 is not available for', select);
            }
        });
    };

    // Initialize both libraries
    initSelect2();
    initSingleSelect2();
    // initSummernote();
    
    // Make functions available globally for re-initialization
    window.initSelect2 = initSelect2;
    window.initSingleSelect2 = initSingleSelect2;
    // window.initSummernote = initSummernote;
    
    console.log('All JavaScript libraries initialized successfully');
});
