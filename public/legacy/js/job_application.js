(function(){
  const modalEl = document.getElementById('applicationModal');
  if (!modalEl) return;
  const appJobTitle = document.getElementById('appJobTitle');
  const appJobId = document.getElementById('appJobId');
  const appEmployerId = document.getElementById('appEmployerId');
  const form = document.getElementById('jobApplicationForm');
  const alertBox = document.getElementById('appAlert');
  const submitBtn = document.getElementById('submitAppBtn');
  const spinner = document.getElementById('submitSpinner');
  const useSavedChk = document.getElementById('useSavedResumeChk');
  const useSavedInput = document.getElementById('useSavedResumeInput');
  const resumeInput = document.getElementById('resume');
  let lastJobId = 0;
  let submitSucceeded = false;

  // Defensive: initialize saved resume toggle state on load
  if (useSavedChk && useSavedInput) {
    const enabled = !useSavedChk.disabled && !!useSavedChk.checked;
    useSavedInput.value = enabled ? '1' : '0';
    if (resumeInput) {
      resumeInput.disabled = enabled;
    }
  }

  function showAlert(type, msg){
    alertBox.className = 'alert alert-' + type;
    alertBox.textContent = msg;
    alertBox.classList.remove('d-none');
  }

  function clearAlert(){
    alertBox.classList.add('d-none');
    alertBox.textContent = '';
  }

  document.addEventListener('click', function(e){
    const btn = e.target.closest('.apply-job-btn');
    if (!btn) return;
    const title = btn.getAttribute('data-job-title') || '';
    const jobId = btn.getAttribute('data-job-id') || '';
    const employerId = btn.getAttribute('data-employer-id') || '';
    if (appJobTitle) appJobTitle.textContent = title;
    if (appJobId) appJobId.value = jobId;
    lastJobId = parseInt(jobId || '0', 10) || 0;
    submitSucceeded = false;
    if (appEmployerId) appEmployerId.value = employerId;
    // Reset alerts and file state when opening
    clearAlert();
    if (useSavedChk) {
      const enabled = !useSavedChk.disabled && useSavedChk.checked;
      if (useSavedInput) useSavedInput.value = enabled ? '1' : '0';
      if (resumeInput) {
        resumeInput.disabled = enabled;
        if (enabled) resumeInput.value = '';
      }
    }
  });

  // Toggle saved resume vs file upload
  if (useSavedChk) {
    useSavedChk.addEventListener('change', function(){
      const enabled = useSavedChk.checked;
      if (useSavedInput) useSavedInput.value = enabled ? '1' : '0';
      if (resumeInput) {
        resumeInput.disabled = enabled;
        if (enabled) resumeInput.value = '';
      }
    });
  }

  function validateFile(file){
    if (!file) return 'Please attach your resume.';
    const max = 5 * 1024 * 1024;
    if (file.size > max) return 'File too large. Max 5MB.';
    const ok = [
      'application/pdf',
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    const ext = (file.name.split('.').pop() || '').toLowerCase();
    const extOk = ['pdf','doc','docx'];
    if (!ok.includes(file.type) && !extOk.includes(ext)) return 'Unsupported file type. Use PDF, DOC, or DOCX.';
    return '';
  }

  form.addEventListener('submit', async function(e){
    e.preventDefault();
    clearAlert();
    const usingSaved = useSavedInput && useSavedInput.value === '1';
    let file = null;
    if (!usingSaved) {
      file = resumeInput ? resumeInput.files[0] : null;
      const err = validateFile(file);
      if (err){ showAlert('warning', err); return; }
    }

    const fd = new FormData(form);
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    try {
      const res = await fetch('process_application.php', { method: 'POST', body: fd });
      const data = await res.json().catch(()=>({success:false,message:'Invalid server response.'}));
      if (!res.ok || !data.success){
        showAlert('danger', data.message || 'Submission failed.');
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
        return;
      }
      showAlert('success', 'Application submitted successfully.');
      try {
        const jobIdVal = appJobId ? parseInt(appJobId.value || '0', 10) : 0;
        const payload = { jobId: jobIdVal, ts: Date.now() };
        localStorage.setItem('job_applied', JSON.stringify(payload));
        setTimeout(()=> localStorage.removeItem('job_applied'), 50);
        // Immediately reflect on this page: flip the Apply button to Applied
        if (jobIdVal) {
          document.querySelectorAll('.apply-job-btn, .apply-btn').forEach(btn => {
            if (String(btn.getAttribute('data-job-id')) === String(jobIdVal)) {
              btn.textContent = 'Applied';
              btn.classList.remove('apply-job-btn');
              btn.removeAttribute('data-bs-toggle');
              btn.removeAttribute('data-bs-target');
              btn.setAttribute('aria-disabled', 'true');
              btn.style.pointerEvents = 'none';
              btn.style.opacity = '0.8';
            }
          });
        }
        submitSucceeded = true;
      } catch(_) {}
      form.reset();
      setTimeout(()=>{
        const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        bsModal.hide();
      }, 800);
    } catch (err) {
      showAlert('danger', 'Network error. Please try again.');
      submitBtn.disabled = false;
    } finally {
      spinner.classList.add('d-none');
      submitBtn.disabled = false;
    }
  });

  // If the modal is closed without a successful submit, ensure buttons remain actionable
  modalEl.addEventListener('hidden.bs.modal', function(){
    if (!submitSucceeded && lastJobId) {
      document.querySelectorAll('.job-card .apply-btn, .job-card .apply-job-btn').forEach(btn => {
        if (String(btn.getAttribute('data-job-id')) === String(lastJobId)) {
          // Restore Apply Now state and modal trigger
          btn.textContent = 'Apply Now';
          btn.classList.add('apply-job-btn');
          btn.setAttribute('data-bs-toggle', 'modal');
          btn.setAttribute('data-bs-target', '#applicationModal');
          btn.removeAttribute('aria-disabled');
          btn.style.pointerEvents = '';
          btn.style.opacity = '';
        }
      });
    }
    // Reset file input toggle
    if (useSavedChk && !useSavedChk.disabled) {
      const enabled = useSavedChk.checked;
      if (useSavedInput) useSavedInput.value = enabled ? '1' : '0';
      if (resumeInput) {
        resumeInput.disabled = enabled;
        if (enabled) resumeInput.value = '';
      }
    }
  });
})();
