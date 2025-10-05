$(document).ready(function() {
    // Filter rooms by property
    $('#property_id').on('change', function() {
        const propertyId = $(this).val();
        const roomSelect = $('#room_id');
        
        roomSelect.find('option').hide();
        roomSelect.find('option[value=""]').show();
        
        if (propertyId) {
            roomSelect.find(`option[data-property-id="${propertyId}"]`).show();
        } else {
            roomSelect.find('option').show();
        }
        
        roomSelect.val('');
    });

    // Show/hide sections based on room type
    $('#room_id').on('change', function() {
        const roomType = $(this).find(':selected').data('room-type');
        
        // Hide all sections first
        $('.kitchen-section, .living-section').hide();
        
        // Show sections based on room type
        if (roomType && (roomType.includes('Suite') || roomType.includes('Apartment'))) {
            $('.kitchen-section, .living-section').show();
        } else if (roomType && roomType.includes('Studio')) {
            $('.living-section').show();
        }
    });

    // Auto-set estimated duration based on checklist type and room type
    $('#checklist_type, #room_id').on('change', function() {
        const type = $('#checklist_type').val();
        const roomType = $('#room_id').find(':selected').data('room-type');
        
        let duration = 60; // default
        
        if (type === 'deep_clean') {
            duration = 120;
        } else if (type === 'inspection') {
            duration = 30;
        } else if (type === 'maintenance') {
            duration = 90;
        }
        
        // Adjust for room type
        if (roomType && (roomType.includes('Suite') || roomType.includes('Apartment'))) {
            duration += 30;
        }
        
        $('#estimated_duration').val(duration);
    });
});

function selectAll() {
    $('input[name="checklist_items[]"]:visible').prop('checked', true);
}

function clearAll() {
    $('input[name="checklist_items[]"]').prop('checked', false);
}

function selectByType() {
    const type = $('#checklist_type').val();
    
    // Clear all first
    clearAll();
    
    // Select items based on type
    if (type === 'checkout') {
        // Basic cleaning items
        $('#bathroom_toilet_clean, #bathroom_shower_clean, #bathroom_sink_clean, #bathroom_mirror_clean, #bathroom_floor_mop, #bathroom_towels_replace, #bathroom_amenities_stock, #bathroom_trash_empty').prop('checked', true);
        $('#bedroom_bed_make, #bedroom_vacuum, #bedroom_trash_empty').prop('checked', true);
    } else if (type === 'deep_clean') {
        // All items
        selectAll();
    } else if (type === 'inspection') {
        // Inspection items
        $('#bathroom_mirror_clean, #bedroom_bed_make, #bedroom_nightstands_clean').prop('checked', true);
    }
}