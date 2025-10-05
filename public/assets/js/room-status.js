$(document).ready(function() {
    // Room status card click
    $('.room-status-card').on('click', function(e) {
        if (!$(e.target).is('button, i')) {
            var roomStatusId = $(this).data('room-status-id');
            showRoomDetails(roomStatusId);
        }
    });

    // Assign button
    $('.assign-btn').on('click', function(e) {
        e.stopPropagation();
        var roomStatusId = $(this).data('room-status-id');
        showAssignModal(roomStatusId);
    });

    // Start button
    $('.start-btn').on('click', function(e) {
        e.stopPropagation();
        var roomStatusId = $(this).data('room-status-id');
        startWork(roomStatusId);
    });

    // Complete button
    $('.complete-btn').on('click', function(e) {
        e.stopPropagation();
        var roomStatusId = $(this).data('room-status-id');
        showCompleteModal(roomStatusId);
    });

    // Inspect button
    $('.inspect-btn').on('click', function(e) {
        e.stopPropagation();
        var roomStatusId = $(this).data('room-status-id');
        showInspectModal(roomStatusId);
    });

    // Modal action buttons
    $(document).on('click', '#modal-assign-btn', function(e) {
        var roomStatusId = $(this).data('room-status-id');
        const roomDetailsModal = bootstrap.Modal.getInstance(document.getElementById('roomDetailsModal'));
        roomDetailsModal.hide();
        showAssignModal(roomStatusId);
    });

    $(document).on('click', '#modal-start-btn', function(e) {
        var roomStatusId = $(this).data('room-status-id');
        const roomDetailsModal = bootstrap.Modal.getInstance(document.getElementById('roomDetailsModal'));
        roomDetailsModal.hide();
        startWork(roomStatusId);
    });

    $(document).on('click', '#modal-complete-btn', function(e) {
        var roomStatusId = $(this).data('room-status-id');
        const roomDetailsModal = bootstrap.Modal.getInstance(document.getElementById('roomDetailsModal'));
        roomDetailsModal.hide();
        showCompleteModal(roomStatusId);
    });

    $(document).on('click', '#modal-inspect-btn', function(e) {
        var roomStatusId = $(this).data('room-status-id');
        const roomDetailsModal = bootstrap.Modal.getInstance(document.getElementById('roomDetailsModal'));
        roomDetailsModal.hide();
        showInspectModal(roomStatusId);
    });
});

function showRoomDetails(roomStatusId) {
    // Load room details via AJAX and show modal
    $.get('/room-status/' + roomStatusId)
        .done(function(data) {
            console.log('Room details data:', data); // Debugging line
            populateRoomDetailsModal(data);
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('roomDetailsModal'));
            modal.show();
        })
        .fail(function(xhr, status, error) {
            console.error('Error loading room details:', error);
            alert('Failed to load room details. Please try again.');
        });
}

function populateRoomDetailsModal(data) {
    const roomStatus = data.room_status;
    const room = roomStatus.room;
    const property = roomStatus.property;
    
    // Basic room information
    $('#modal-room-number').text(room.number);
    $('#modal-room-name').text(room.name);
    $('#modal-room-type').text(room.type.name);
    $('#modal-property-name').text(property.name);
    $('#modal-property-code').text(property.code);
    
    // Room details
    $('#modal-room-floor').text(room.floor || 'Not specified');
    $('#modal-room-description').text(room.description || 'No description available');
    $('#modal-room-capacity').text(`${room.type.base_capacity} - ${room.type.max_capacity} guests`);
    
    // Status information
    $('#modal-current-status').html(`<span class="badge badge-${getStatusColor(roomStatus.status)}">${formatStatus(roomStatus.status)}</span>`);
    $('#modal-housekeeping-status').html(`<span class="badge badge-${getHousekeepingStatusColor(roomStatus.housekeeping_status)}">${formatStatus(roomStatus.housekeeping_status)}</span>`);
    
    // Assignment information
    if (roomStatus.assigned_to) {
        $('#modal-assigned-staff').text(roomStatus.assignedTo ? roomStatus.assignedTo.name : 'Unknown Staff');
        $('#modal-assigned-at').text(formatDateTime(roomStatus.assigned_at));
    } else {
        $('#modal-assigned-staff').text('Not assigned');
        $('#modal-assigned-at').text('-');
    }
    
    // Timing information
    $('#modal-started-at').text(roomStatus.started_at ? formatDateTime(roomStatus.started_at) : '-');
    $('#modal-completed-at').text(roomStatus.completed_at ? formatDateTime(roomStatus.completed_at) : '-');
    $('#modal-inspected-at').text(roomStatus.inspected_at ? formatDateTime(roomStatus.inspected_at) : '-');
    
    // Inspector information
    if (roomStatus.inspected_by) {
        $('#modal-inspected-by').text(roomStatus.inspectedBy ? roomStatus.inspectedBy.name : 'Unknown Inspector');
    } else {
        $('#modal-inspected-by').text('-');
    }
    
    // Notes
    $('#modal-notes').text(roomStatus.notes || 'No notes available');
    
    // Last status change
    $('#modal-status-changed').text(formatDateTime(roomStatus.status_changed_at));
    
    // Action buttons visibility
    updateModalActionButtons(data);
}

function updateModalActionButtons(data) {
    const canAssign = data.can_assign;
    const canStart = data.can_start;
    const canComplete = data.can_complete;
    const roomStatusId = data.room_status.id;
    
    // Show/hide action buttons based on permissions
    if (canAssign) {
        $('#modal-assign-btn').show().data('room-status-id', roomStatusId);
    } else {
        $('#modal-assign-btn').hide();
    }
    
    if (canStart) {
        $('#modal-start-btn').show().data('room-status-id', roomStatusId);
    } else {
        $('#modal-start-btn').hide();
    }
    
    if (canComplete) {
        $('#modal-complete-btn').show().data('room-status-id', roomStatusId);
    } else {
        $('#modal-complete-btn').hide();
    }
    
    // Always show inspect button if work is completed
    if (data.room_status.housekeeping_status === 'completed') {
        $('#modal-inspect-btn').show().data('room-status-id', roomStatusId);
    } else {
        $('#modal-inspect-btn').hide();
    }
}

// Helper functions
function getStatusColor(status) {
    const colors = {
        'dirty': 'danger',
        'clean': 'success',
        'inspected': 'primary',
        'maintenance': 'warning',
        'out_of_order': 'dark'
    };
    return colors[status] || 'secondary';
}

function getHousekeepingStatusColor(status) {
    const colors = {
        'pending': 'warning',
        'in_progress': 'info',
        'completed': 'success',
        'inspected': 'primary'
    };
    return colors[status] || 'secondary';
}

function formatStatus(status) {
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function formatDateTime(datetime) {
    if (!datetime) return '-';
    const date = new Date(datetime);
    return date.toLocaleString('en-ZA', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function showAssignModal(roomStatusId) {
    $('#assignStaffModal input[name="room_status_id"]').val(roomStatusId);
    const modal = new bootstrap.Modal(document.getElementById('assignStaffModal'));
    modal.show();
}

function startWork(roomStatusId) {
    if (confirm('Start housekeeping work for this room?')) {
        $.post('/room-status/' + roomStatusId + '/start', {
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(data) {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}

function showCompleteModal(roomStatusId) {
    $('#completeWorkModal input[name="room_status_id"]').val(roomStatusId);
    const modal = new bootstrap.Modal(document.getElementById('completeWorkModal'));
    modal.show();
}

function showInspectModal(roomStatusId) {
    $('#inspectRoomModal input[name="room_status_id"]').val(roomStatusId);
    const modal = new bootstrap.Modal(document.getElementById('inspectRoomModal'));
    modal.show();
}