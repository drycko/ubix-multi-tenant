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

    // Auto-fill title based on selections
    $('#room_id, #task_type').on('change', function() {
        const roomText = $('#room_id option:selected').text();
        const taskType = $('#task_type option:selected').text();
        
        if (roomText && taskType && roomText !== 'Select Room' && taskType !== 'Select Task Type') {
            const roomNumber = roomText.split(' - ')[0];
            $('#title').val(`${taskType} - ${roomNumber}`);
        }
    });

    // Set default estimated time based on task type
    $('#task_type').on('change', function() {
        const taskType = $(this).val();
        const estimates = {
            'cleaning': 30,
            'maintenance': 45,
            'inspection': 15,
            'deep_clean': 90,
            'setup': 20
        };
        
        if (estimates[taskType]) {
            $('#estimated_minutes').val(estimates[taskType]);
        }
    });
});