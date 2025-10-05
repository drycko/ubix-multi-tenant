<!-- Bulk Assign Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1" aria-labelledby="bulkAssignModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('tenant.room-status.bulk-assign') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="bulkAssignModalLabel">Bulk Assign Rooms</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="bulk_assigned_to" class="form-label">Assign To</label>
            <select name="assigned_to" id="bulk_assigned_to" class="form-select" required>
              <option value="">Select Staff Member</option>
              @foreach($staff as $member)
              <option value="{{ $member->id }}">{{ $member->name }}</option>
              @endforeach
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Select Rooms to Assign</label>
            <div class="row" style="max-height: 300px; overflow-y: auto;">
              @foreach($roomStatuses->where('housekeeping_status', 'pending') as $roomStatus)
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="room_status_ids[]" 
                         value="{{ $roomStatus->id }}" id="room_{{ $roomStatus->id }}">
                  <label class="form-check-label" for="room_{{ $roomStatus->id }}">
                    Room {{ $roomStatus->room->number }} 
                    <small class="text-muted">({{ $roomStatus->room->type->name }})</small>
                  </label>
                </div>
              </div>
              @endforeach
            </div>
          </div>
          
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="select_all_rooms">
            <label class="form-check-label" for="select_all_rooms">
              Select All Available Rooms
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Assign Rooms</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('select_all_rooms').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="room_status_ids[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});
</script>