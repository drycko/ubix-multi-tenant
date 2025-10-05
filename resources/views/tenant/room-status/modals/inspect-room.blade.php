<!-- Inspect Room Modal -->
<div class="modal fade" id="inspectRoomModal" tabindex="-1" aria-labelledby="inspectRoomModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="#" id="inspectRoomForm">
        @csrf
        <input type="hidden" name="room_status_id" value="">
        
        <div class="modal-header">
          <h5 class="modal-title" id="inspectRoomModalLabel">Room Inspection</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Inspection Result</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="passed" id="inspection_passed" value="1" required>
              <label class="form-check-label text-success" for="inspection_passed">
                <i class="bi bi-check-circle"></i> <strong>Passed</strong> - Room meets standards
              </label>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="radio" name="passed" id="inspection_failed" value="0" required>
              <label class="form-check-label text-danger" for="inspection_failed">
                <i class="bi bi-x-circle"></i> <strong>Failed</strong> - Room needs additional work
              </label>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="inspection_notes" class="form-label">Inspection Notes</label>
            <textarea name="inspection_notes" id="inspection_notes" class="form-control" rows="3" 
                      placeholder="Inspection findings, issues noted, etc..."></textarea>
          </div>
          
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Note:</strong> If the room fails inspection, it will be reset to "Dirty" status and will need to be cleaned again.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Inspection</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('inspectRoomForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const roomStatusId = this.querySelector('input[name="room_status_id"]').value;
    const passed = this.querySelector('input[name="passed"]:checked');
    const inspectionNotes = this.querySelector('textarea[name="inspection_notes"]').value;
    
    if (!passed) {
        alert('Please select an inspection result');
        return;
    }
    // log the values to verify
    console.log('Inspecting room status ID:', roomStatusId, 'Passed:', passed.value, 'Notes:', inspectionNotes);
    const passedValue = passed.value == '1' ? true : false;
    // Use jQuery $.post like the working complete function
    $.post(`/room-status/${roomStatusId}/inspect`, {
        passed: passedValue,
        inspection_notes: inspectionNotes,
        _token: $('meta[name="csrf-token"]').attr('content')
    })
    .done(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Inspection failed');
        }
    })
    .fail(function(xhr, status, error) {
        console.error('Inspection failed:', {
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
            errorMessage = 'Room not found.';
        } else if (xhr.status === 500) {
            errorMessage = 'Server error occurred.';
        }

        alert(errorMessage);
    });
});
</script>