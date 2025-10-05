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
    
    fetch(`/room-status/${roomStatusId}/inspect`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            passed: passed.value === '1',
            inspection_notes: inspectionNotes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
    });
});
</script>