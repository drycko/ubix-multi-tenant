<!-- Room Details Modal -->
<div class="modal fade" id="roomDetailsModal" tabindex="-1" aria-labelledby="roomDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="roomDetailsModalLabel">
          <i class="bi bi-door-open"></i> Room <span id="modal-room-number">-</span> Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <!-- Left Column - Room Information -->
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-header">
                <h6 class="card-title mb-0"><i class="bi bi-info-circle"></i> Room Information</h6>
              </div>
              <div class="card-body">
                <table class="table table-sm">
                  <tbody>
                    <tr>
                      <td><strong>Room Number:</strong></td>
                      <td id="modal-room-number">-</td>
                    </tr>
                    <tr>
                      <td><strong>Room Name:</strong></td>
                      <td id="modal-room-name">-</td>
                    </tr>
                    <tr>
                      <td><strong>Room Type:</strong></td>
                      <td id="modal-room-type">-</td>
                    </tr>
                    <tr>
                      <td><strong>Floor:</strong></td>
                      <td id="modal-room-floor">-</td>
                    </tr>
                    <tr>
                      <td><strong>Capacity:</strong></td>
                      <td id="modal-room-capacity">-</td>
                    </tr>
                    <tr>
                      <td><strong>Property:</strong></td>
                      <td><span id="modal-property-name">-</span> (<span id="modal-property-code">-</span>)</td>
                    </tr>
                  </tbody>
                </table>
                
                <div class="mt-3">
                  <h6><i class="bi bi-card-text"></i> Description</h6>
                  <p class="text-muted" id="modal-room-description">-</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Status Information -->
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-header">
                <h6 class="card-title mb-0"><i class="bi bi-clipboard-check"></i> Status Information</h6>
              </div>
              <div class="card-body">
                <table class="table table-sm">
                  <tbody>
                    <tr>
                      <td><strong>Current Status:</strong></td>
                      <td id="modal-current-status">-</td>
                    </tr>
                    <tr>
                      <td><strong>Housekeeping Status:</strong></td>
                      <td id="modal-housekeeping-status">-</td>
                    </tr>
                    <tr>
                      <td><strong>Last Status Change:</strong></td>
                      <td id="modal-status-changed">-</td>
                    </tr>
                  </tbody>
                </table>

                <h6 class="mt-3"><i class="bi bi-person"></i> Staff Assignment</h6>
                <table class="table table-sm">
                  <tbody>
                    <tr>
                      <td><strong>Assigned To:</strong></td>
                      <td id="modal-assigned-staff">-</td>
                    </tr>
                    <tr>
                      <td><strong>Assigned At:</strong></td>
                      <td id="modal-assigned-at">-</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Timeline Row -->
        <div class="row mt-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h6 class="card-title mb-0"><i class="bi bi-clock-history"></i> Work Timeline</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-3">
                    <div class="text-center">
                      <div class="timeline-icon bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-play"></i>
                      </div>
                      <h6 class="mt-2 mb-1">Started</h6>
                      <small class="text-muted" id="modal-started-at">-</small>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="text-center">
                      <div class="timeline-icon bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-check"></i>
                      </div>
                      <h6 class="mt-2 mb-1">Completed</h6>
                      <small class="text-muted" id="modal-completed-at">-</small>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="text-center">
                      <div class="timeline-icon bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-shield-check"></i>
                      </div>
                      <h6 class="mt-2 mb-1">Inspected</h6>
                      <small class="text-muted" id="modal-inspected-at">-</small>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="text-center">
                      <div class="timeline-icon bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-person"></i>
                      </div>
                      <h6 class="mt-2 mb-1">Inspector</h6>
                      <small class="text-muted" id="modal-inspected-by">-</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes Row -->
        <div class="row mt-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h6 class="card-title mb-0"><i class="bi bi-sticky"></i> Notes</h6>
              </div>
              <div class="card-body">
                <p class="mb-0" id="modal-notes">No notes available</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <!-- Action Buttons (shown/hidden based on status) -->
        <button type="button" class="btn btn-outline-primary" id="modal-assign-btn" style="display: none;">
          <i class="bi bi-person-plus"></i> Assign Staff
        </button>
        <button type="button" class="btn btn-outline-success" id="modal-start-btn" style="display: none;">
          <i class="bi bi-play"></i> Start Work
        </button>
        <button type="button" class="btn btn-outline-warning" id="modal-complete-btn" style="display: none;">
          <i class="bi bi-check"></i> Complete Work
        </button>
        <button type="button" class="btn btn-outline-info" id="modal-inspect-btn" style="display: none;">
          <i class="bi bi-shield-check"></i> Inspect Room
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>