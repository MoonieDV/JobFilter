<div class="modal fade" id="interviewModal" tabindex="-1" aria-labelledby="interviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="interviewModalLabel">Schedule Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="interviewForm">
                    <input type="hidden" id="applicationId" name="application_id">
                    
                    <div class="mb-3">
                        <label for="scheduledAt" class="form-label">Interview Date & Time</label>
                        <input type="datetime-local" class="form-control" id="scheduledAt" name="scheduled_at" required>
                    </div>

                    <div class="mb-3">
                        <label for="interviewType" class="form-label">Interview Type</label>
                        <select class="form-select" id="interviewType" name="interview_type" required>
                            <option value="">Select type...</option>
                            <option value="online">Online</option>
                            <option value="physical">Physical</option>
                        </select>
                    </div>

                    <div class="alert alert-info" id="letterPreview" style="display: none;">
                        <strong>Interview Letter Preview:</strong>
                        <p id="letterText" class="mt-2"></p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="scheduleBtn">Schedule Interview</button>
            </div>
        </div>
    </div>
</div>
