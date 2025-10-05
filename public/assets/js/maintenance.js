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

    // Auto-set priority based on category for some urgent categories
    $('#category').on('change', function() {
        const category = $(this).val();
        const urgentCategories = ['electrical', 'plumbing'];
        
        if (urgentCategories.includes(category) && !$('#priority').val()) {
            $('#priority').val('high');
        }
    });

    // Auto-suggest due date based on priority
    $('#priority').on('change', function() {
        const priority = $(this).val();
        const today = new Date();
        let dueDate;
        
        switch(priority) {
            case 'urgent':
                dueDate = new Date(today.getTime() + 24 * 60 * 60 * 1000); // 1 day
                break;
            case 'high':
                dueDate = new Date(today.getTime() + 3 * 24 * 60 * 60 * 1000); // 3 days
                break;
            case 'normal':
                dueDate = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000); // 1 week
                break;
            case 'low':
                dueDate = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000); // 1 month
                break;
        }
        
        if (dueDate && !$('#due_date').val()) {
            $('#due_date').val(dueDate.toISOString().split('T')[0]);
        }
    });

    // Photo preview
    $('#photos').on('change', function() {
        const files = this.files;
        $('.photo-preview').remove();
        
        for (let i = 0; i < Math.min(files.length, 5); i++) {
            const file = files[i];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const preview = $(`
                    <div class="photo-preview mb-2">
                        <img src="${e.target.result}" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                        <small class="d-block text-muted">${file.name}</small>
                    </div>
                `);
                $('#photos').after(preview);
            };
            
            reader.readAsDataURL(file);
        }
    });
});

$(document).ready(function() {
    $('.complete-btn').on('click', function() {
        const requestId = $(this).data('request-id');
        $('#completeMaintenanceForm').attr('action', `/maintenance/${requestId}/complete`);
        $('#completeWorkModal').modal('show');
    });
});

// Photo modal
$('#photoModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget);
    const photo = button.data('photo');
    $('#modalPhoto').attr('src', photo);
});