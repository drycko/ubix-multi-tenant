$(document).ready(function() {
    // Update preview as user types
    $('#name').on('input', function() {
        const value = $(this).val() || 'Enter permission name';
        $('#preview-name').text(value);
    });

    $('#display_name').on('input', function() {
        const value = $(this).val() || 'Not set';
        $('#preview-display').text(value);
    });

    $('#description').on('input', function() {
        const value = $(this).val() || 'Enter description';
        $('#preview-description').text(value);
    });

    // Reset preview on form reset
    $('form').on('reset', function() {
        setTimeout(function() {
            $('#preview-name').text('Enter permission name');
            $('#preview-display').text('Not set');
            $('#preview-description').text('Enter description');
        }, 10);
    });
});

function fillExample(action) {
    const examples = {
        'view': {
            name: 'view bookings',
            display: 'View Bookings',
            description: 'Allows user to view booking information and details'
        },
        'create': {
            name: 'create rooms',
            display: 'Create Rooms',
            description: 'Allows user to create new rooms and room configurations'
        },
        'edit': {
            name: 'edit guests',
            display: 'Edit Guests',
            description: 'Allows user to modify guest information and profiles'
        },
        'delete': {
            name: 'delete users',
            display: 'Delete Users',
            description: 'Allows user to delete user accounts from the system'
        },
        'manage': {
            name: 'manage settings',
            display: 'Manage Settings',
            description: 'Allows user to configure and modify system settings'
        }
    };

    const example = examples[action];
    if (example) {
        $('#name').val(example.name).trigger('input');
        $('#display_name').val(example.display).trigger('input');
        $('#description').val(example.description).trigger('input');
    }
}