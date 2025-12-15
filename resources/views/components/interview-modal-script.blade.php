<script>
document.addEventListener('DOMContentLoaded', function() {
    const interviewModal = document.getElementById('interviewModal');
    const interviewForm = document.getElementById('interviewForm');
    const applicationIdInput = document.getElementById('applicationId');
    const scheduledAtInput = document.getElementById('scheduledAt');
    const interviewTypeSelect = document.getElementById('interviewType');
    const letterPreview = document.getElementById('letterPreview');
    const letterText = document.getElementById('letterText');
    const scheduleBtn = document.getElementById('scheduleBtn');

    // Handle interview button clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-interview')) {
            const applicationId = e.target.dataset.applicationId;
            applicationIdInput.value = applicationId;
            scheduledAtInput.value = '';
            interviewTypeSelect.value = '';
            letterPreview.style.display = 'none';
            
            const modal = new bootstrap.Modal(interviewModal);
            modal.show();
        }
    });

    // Update letter preview
    function updateLetterPreview() {
        const scheduledAt = scheduledAtInput.value;
        const interviewType = interviewTypeSelect.value;

        if (scheduledAt && interviewType) {
            const date = new Date(scheduledAt);
            const formattedDate = date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            letterText.textContent = `Hi! We would like to interview you on ${formattedDate} via ${interviewType.charAt(0).toUpperCase() + interviewType.slice(1)}.`;
            letterPreview.style.display = 'block';
        } else {
            letterPreview.style.display = 'none';
        }
    }

    scheduledAtInput.addEventListener('change', updateLetterPreview);
    interviewTypeSelect.addEventListener('change', updateLetterPreview);

    // Handle schedule button click
    scheduleBtn.addEventListener('click', async function() {
        const applicationId = applicationIdInput.value;
        const scheduledAt = scheduledAtInput.value;
        const interviewType = interviewTypeSelect.value;

        if (!applicationId || !scheduledAt || !interviewType) {
            alert('Please fill in all fields');
            return;
        }

        try {
            scheduleBtn.disabled = true;
            scheduleBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Scheduling...';

            const response = await fetch('{{ route("interview-schedules.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    application_id: applicationId,
                    scheduled_at: scheduledAt,
                    interview_type: interviewType
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to schedule interview');
            }

            // Close modal
            bootstrap.Modal.getInstance(interviewModal).hide();

            // Update the button to show scheduled status
            const button = document.querySelector(`[data-application-id="${applicationId}"].btn-interview`);
            if (button) {
                button.classList.remove('btn-primary', 'btn-interview');
                button.classList.add('btn-secondary', 'disabled');
                button.disabled = true;
                button.innerHTML = '<i class="bi bi-calendar-check me-1"></i>Scheduled';
            }

            // Show success message
            alert('Interview scheduled successfully!');
            
            // Reload page to reflect changes
            setTimeout(() => {
                location.reload();
            }, 1000);

        } catch (error) {
            console.error('Error:', error);
            alert('Error scheduling interview: ' + error.message);
        } finally {
            scheduleBtn.disabled = false;
            scheduleBtn.innerHTML = 'Schedule Interview';
        }
    });
});
</script>
