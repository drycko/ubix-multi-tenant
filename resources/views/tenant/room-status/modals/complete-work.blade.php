<!-- Complete Work Modal -->
<div class="modal fade" id="completeWorkModal" tabindex="-1" aria-labelledby="completeWorkModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="#" id="completeWorkForm">
        @csrf
        <input type="hidden" name="room_status_id" value="">
        
        <div class="modal-header">
          <h5 class="modal-title" id="completeWorkModalLabel">Complete Housekeeping Work</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="completion_notes" class="form-label">Completion Notes</label>
            <textarea name="completion_notes" id="completion_notes" class="form-control" rows="3" 
                      placeholder="Any notes about the work completed..."></textarea>
          </div>
          
          <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Completing this work will mark the room as <strong>Clean</strong> and ready for inspection.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Complete Work</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('completeWorkForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const roomStatusId = this.querySelector('input[name="room_status_id"]').value;
    const completionNotes = this.querySelector('textarea[name="completion_notes"]').value;
    
    fetch(`/room-status/${roomStatusId}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            completion_notes: completionNotes
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