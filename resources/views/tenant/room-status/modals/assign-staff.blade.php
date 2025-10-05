<!-- Assign Staff Modal -->
<div class="modal fade" id="assignStaffModal" tabindex="-1" aria-labelledby="assignStaffModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="#" id="assignStaffForm">
        @csrf
        <input type="hidden" name="room_status_id" value="">
        
        <div class="modal-header">
          <h5 class="modal-title" id="assignStaffModalLabel">Assign Staff to Room</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="assigned_to" class="form-label">Assign To</label>
            <select name="assigned_to" id="assigned_to" class="form-select" required>
              <option value="">Select Staff Member</option>
              @foreach($staff as $member)
              <option value="{{ $member->id }}">{{ $member->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Assign</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('assignStaffForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const roomStatusId = this.querySelector('input[name="room_status_id"]').value;
    const assignedTo = this.querySelector('select[name="assigned_to"]').value;
    
    if (!assignedTo) {
        alert('Please select a staff member');
        return;
    }
    
    console.log('Assigning room status ID:', roomStatusId, 'to staff:', assignedTo);
    
    // Use jQuery $.post like the working startWork function
    $.post(`/room-status/${roomStatusId}/assign`, {
        assigned_to: assignedTo,
        _token: $('meta[name="csrf-token"]').attr('content')
    })
    .done(function(data) {
        console.log('Assignment response:', data);
        
        if (data.success) {
            // Close modal first
            const modal = bootstrap.Modal.getInstance(document.getElementById('assignStaffModal'));
            modal.hide();
            location.reload();
        } else {
            alert(data.message || 'Assignment failed');
        }
    })
    .fail(function(xhr, status, error) {
        console.error('Assignment failed:', {
            status: xhr.status,
            statusText: xhr.statusText,
            responseText: xhr.responseText,
            error: error
        });
        
        let errorMessage = 'An error occurred. Please try again.';
        
        if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
        } else if (xhr.status === 422) {
            errorMessage = 'Validation error: Please check your input.';
        } else if (xhr.status === 404) {
            errorMessage = 'Room or staff member not found.';
        } else if (xhr.status === 500) {
            errorMessage = 'Server error occurred.';
        }
        
        alert(errorMessage);
    });
});
</script>