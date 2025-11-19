@php
    use Illuminate\Support\Str;
    $role = $user->role ?? 'guest';
    $appliedLookup = collect($appliedJobIds ?? [])->flip();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Find Jobs - JobFilter</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('legacy/Images/log.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('legacy/css/home.css') }}" rel="stylesheet">
    <link href="{{ asset('legacy/css/jobs.css') }}" rel="stylesheet">
</head>
<body data-user-role="{{ $role }}">
    <nav class="navbar navbar-light bg-light sticky-top shadow-sm navbar-glass">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">JobFilter</a>
            <div class="d-flex align-items-center">
                @auth
                    <div class="dropdown me-3">
                        <a class="nav-link position-relative" href="#" role="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell fs-5"></i>
                            @if ($notifUnreadCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $notifUnreadCount }}
                                    <span class="visually-hidden">unread notifications</span>
                                </span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            @forelse ($notifications as $notification)
                                <li class="notification-item" data-notif-id="{{ $notification->id }}">
                                    <div class="dropdown-item d-flex align-items-start justify-content-between">
                                        <div class="flex-shrink-0">
                                            <i class="bi bi-{{ $notification->type === 'danger' ? 'exclamation-circle text-danger' : ($notification->type === 'success' ? 'check-circle text-success' : 'info-circle text-primary') }}"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-2 me-2">
                                            <p class="mb-0 fw-semibold">{{ $notification->title }}</p>
                                            <small class="text-muted">{{ $notification->message }}</small>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="notification-item">
                                    <div class="dropdown-item text-muted">No notifications</div>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                @endauth
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#navMenu" aria-controls="navMenu" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="offcanvas offcanvas-end" tabindex="-1" id="navMenu" aria-labelledby="navMenuLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="navMenuLabel">Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <div class="text-center mb-3">
                        <img id="offcanvasAvatar" src="{{ asset('legacy/Images/log.png') }}" class="rounded-circle mb-2" width="72" height="72" alt="Avatar">
                        <h5 class="fw-bold mb-2" id="offcanvasName">{{ $user->name ?? 'Guest' }}</h5>
                        <p class="text-muted small mb-2">{{ $user->email ?? '' }}</p>
                        <p class="text-muted small mb-2">Role: {{ Str::headline($role) }}</p>
                        @auth
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Login</a>
                        @endauth
                        <hr class="mt-3">
                    </div>
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0" id="navMenuList">
                        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link active" href="{{ route('jobs.browse') }}">Find Jobs</a></li>
                        @if ($role === 'employer' || $role === 'admin')
                            <li class="nav-item"><a class="nav-link" href="{{ route('employer.jobs.index') }}">Post Job</a></li>
                        @endif
                        <li class="nav-item"><a class="nav-link" href="{{ route('legacy.about') }}">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('legacy.contact') }}">Contact</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <section class="py-5" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10">
                    <h1 class="text-center mb-4 text-white">Find Your Perfect Job Match</h1>
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <form id="jobSearchForm" method="GET">
                                <div class="row g-3">
                                    <div class="col-12 col-md-3">
                                        <input type="text" class="form-control" id="jobTitle" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Job title or keywords">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <input type="text" class="form-control" id="location" name="location" value="{{ $filters['location'] ?? '' }}" placeholder="Location">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <select class="form-select" id="category" name="employment_type">
                                            <option value="">All Categories</option>
                                            @foreach (['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'internship' => 'Internship'] as $value => $label)
                                                <option value="{{ $value }}" @selected(($filters['employment_type'] ?? '') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <input type="text" class="form-control" id="salaryRange" name="salary" value="{{ $filters['salary'] ?? '' }}" placeholder="Salary Range">
                                    </div>
                                    <div class="col-12 col-md-1 d-grid">
                                        <button type="submit" class="btn btn-primary w-100">Search</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 col-lg-3">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Quick Filters</h5>
                        </div>
                        <div class="card-body">
                            <h6>Skills Match</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="skillMatch">
                                <label class="form-check-label" for="skillMatch">High skill match only</label>
                            </div>
                            <hr>
                            <h6>Experience</h6>
                            @foreach (['Entry Level', 'Mid Level', 'Senior Level'] as $level)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="{{ Str::slug($level) }}">
                                    <label class="form-check-label" for="{{ Str::slug($level) }}">{{ $level }}</label>
                                </div>
                            @endforeach
                            <hr>
                            <h6>Job Type</h6>
                            @foreach (['Full Time', 'Part Time', 'Remote'] as $type)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="{{ Str::slug($type) }}">
                                    <label class="form-check-label" for="{{ Str::slug($type) }}">{{ $type }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-9">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4>Job Results (<span id="jobCount">{{ $jobs->total() }}</span>)</h4>
                        <select class="form-select w-auto" id="sortBy">
                            <option value="relevance">Sort by Relevance</option>
                            <option value="date">Sort by Date</option>
                        </select>
                    </div>

                    @forelse ($jobs as $job)
                        @php
                            $skills = $job->required_skills ?? $job->preferred_skills ?? [];
                            if (empty($skills) && $job->experience_level) {
                                $skills = [$job->experience_level, $job->employment_type];
                            }
                            $alreadyApplied = $appliedLookup->has($job->id);
                        @endphp
                        <div class="card shadow-sm mb-4 job-card" data-job-id="{{ $job->id }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                    <div>
                                        <span class="badge bg-light text-primary mb-2">{{ $job->company_name }}</span>
                                        <h5 class="fw-bold">{{ $job->title }}</h5>
                                        <p class="text-muted mb-2">{{ $job->location }}</p>
                                        <div class="d-flex flex-wrap gap-3 text-muted small">
                                            <span><i class="bi bi-briefcase me-1"></i>{{ Str::headline($job->employment_type) }}</span>
                                            @if ($job->salary)
                                                <span><i class="bi bi-cash-stack me-1"></i>{{ number_format($job->salary, 2) }}</span>
                                            @endif
                                            <span><i class="bi bi-calendar me-1"></i>{{ optional($job->published_at)->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column align-items-end gap-2">
                                        @auth
                                            @if (! $alreadyApplied)
                                                <button type="button" class="btn btn-primary btn-apply-job" data-job-id="{{ $job->id }}" data-job-title="{{ e($job->title) }}" data-company="{{ e($job->company_name) }}">Apply Now</button>
                                            @else
                                                <span class="badge bg-success">Applied</span>
                                            @endif
                                        @else
                                            <a href="{{ route('login') }}" class="btn btn-outline-primary">Sign in to Apply</a>
                                        @endauth
                                        <a href="{{ route('jobs.show', $job) }}" class="btn btn-outline-secondary">View Details</a>
                                    </div>
                                </div>
                                <p class="mt-3 text-muted">{{ Str::limit(strip_tags($job->description), 220) }}</p>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach (array_slice($skills, 0, 6) as $skill)
                                        <span class="badge rounded-pill bg-light text-dark">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="fw-bold">No jobs found</h5>
                                <p class="text-muted mb-0">Adjust your filters and try again.</p>
                            </div>
                        </div>
                    @endforelse

                    <div class="mt-4">
                        {{ $jobs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer py-4 bg-dark text-white">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-filter me-2 text-warning"></i>JobFilter
                    </h5>
                    <p class="text-muted">Revolutionizing job search with AI-powered matching technology.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2">
                    <h6 class="fw-bold mb-3">Product</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Features</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Pricing</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">API</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Integrations</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h6 class="fw-bold mb-3">Company</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">About</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Careers</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Blog</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h6 class="fw-bold mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Community</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Status</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Security</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h6 class="fw-bold mb-3">Legal</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Privacy</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Terms</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Cookies</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Licenses</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">&copy; 2025 JobFilter. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-muted">Made with <i class="fas fa-heart text-danger"></i> for job seekers</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Application Modal (for new applications from job listing) -->
    <div class="modal fade" id="applicationModal" tabindex="-1" aria-labelledby="applicationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="applicationModalLabel">Apply for <span id="modalJobTitle"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="applicationPreviewForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" id="modalFullName" class="form-control" value="{{ auth()->user()->name ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="text" id="modalEmail" class="form-control" value="{{ auth()->user()->email ?? '' }}">
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="form-label">Phone</label>
                                <input type="text" id="modalPhone" class="form-control">
                            </div>
                            <div class="col-md-6 mt-2">
                                <label class="form-label">Location</label>
                                <input type="text" id="modalLocation" class="form-control">
                            </div>
                            <div class="col-12 mt-3">
                                <label class="form-label">Resume <small class="text-muted">(PDF, DOC, DOCX, max 5MB)</small></label>

                                <div class="mt-2 small" id="modalCurrentResumeWrap">Current resume: <a id="modalCurrentResumeLink" href="#" target="_blank">-</a></div>

                                <div id="modalResumeArea" class="mt-2">
                                    <p class="small text-muted">No resume uploaded.</p>
                                </div>

                                <div class="form-check mt-3" id="useSavedResumeWrap" style="display:none;">
                                    <input class="form-check-input" type="checkbox" id="useSavedResumeCheckbox">
                                    <label class="form-check-label small" for="useSavedResumeCheckbox">Use my saved resume</label>
                                </div>

                                <div class="mt-2 small text-muted" id="modalSavedFilenameWrap" style="display:none;">Saved: <span id="modalSavedFilename"></span></div>

                                <div class="mt-3" id="modalResumeFileWrap">
                                    <label class="form-label small">Choose File</label>
                                    <input type="file" id="modalResumeFile" class="form-control form-control-sm" accept=".pdf,.doc,.docx">
                                </div>

                                <div class="form-text small text-muted mt-2">Preview is shown for PDF files only.</div>
                            </div>
                            <div class="col-12 mt-3">
                                <label class="form-label">Cover Letter</label>
                                <textarea id="modalCover" class="form-control" rows="5"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="modalSubmitBtn">SUBMIT APPLICATION</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // User's saved resume path (if any)
        const userSavedResumePath = `{{ auth()->user()->resume_path ?? '' }}`;

        const applicationModalEl = document.getElementById('applicationModal');
        const applicationModal = new bootstrap.Modal(applicationModalEl);

        // Helper function to construct proper resume URL
        function getResumeUrl(resumePath) {
            if (!resumePath) return '';
            
            // If already has /storage or http, return as-is
            if (resumePath.startsWith('/storage') || resumePath.startsWith('http')) {
                return resumePath;
            }
            
            // If it's just a filename, prepend /storage/resumes/
            if (!resumePath.includes('/')) {
                return '/storage/resumes/' + resumePath;
            }
            
            // If it starts with resumes/, prepend /storage/
            if (resumePath.startsWith('resumes/')) {
                return '/storage/' + resumePath;
            }
            
            // Otherwise prepend /storage/ to the path, removing leading slash if present
            return '/storage/' + (resumePath.startsWith('/') ? resumePath.slice(1) : resumePath);
        }

        // Helper function to update file input state based on checkbox
        function updateResumeFileState(isUsingSaved) {
            const fileInput = document.getElementById('modalResumeFile');
            const fileWrap = document.getElementById('modalResumeFileWrap');
            
            if (isUsingSaved) {
                fileInput.disabled = true;
                fileWrap.style.opacity = '0.5';
                fileWrap.style.pointerEvents = 'none';
                fileInput.value = '';
            } else {
                fileInput.disabled = false;
                fileWrap.style.opacity = '1';
                fileWrap.style.pointerEvents = 'auto';
            }
        }

        // Helper function to update resume preview area
        function updateResumePreview() {
            const useSavedCheckbox = document.getElementById('useSavedResumeCheckbox');
            const fileInput = document.getElementById('modalResumeFile');
            const resumeArea = document.getElementById('modalResumeArea');

            if (useSavedCheckbox?.checked && userSavedResumePath) {
                // Show saved resume preview
                resumeArea.innerHTML = '';
                const link = document.createElement('a');
                const resumeUrl = getResumeUrl(userSavedResumePath);
                link.href = resumeUrl;
                link.target = '_blank';
                link.textContent = 'Open saved resume';
                link.className = 'btn btn-sm btn-outline-primary';
                resumeArea.appendChild(link);

                // Add PDF preview if it's a PDF
                if (resumeUrl.toLowerCase().endsWith('.pdf')) {
                    const container = document.createElement('div');
                    container.className = 'mt-2 border position-relative';
                    container.style.minHeight = '300px';
                    container.style.backgroundColor = '#f8f9fa';
                    
                    const iframe = document.createElement('iframe');
                    iframe.src = resumeUrl;
                    iframe.style.width = '100%';
                    iframe.style.height = '300px';
                    iframe.style.border = 'none';
                    
                    iframe.onerror = function() {
                        container.innerHTML = '<div style="padding: 20px; text-align: center; color: #dc3545;">Unable to load PDF preview. <a href="' + resumeUrl + '" target="_blank">Click here to open</a></div>';
                    };
                    
                    container.appendChild(iframe);
                    resumeArea.appendChild(container);
                }
            } else if (fileInput?.files?.length > 0) {
                // Show newly selected file preview
                const file = fileInput.files[0];
                resumeArea.innerHTML = '<p class="small text-muted">Selected: <strong>' + file.name + '</strong> (' + (file.size / 1024).toFixed(2) + ' KB)</p>';
            } else {
                // No resume selected
                resumeArea.innerHTML = '<p class="small text-muted">No resume selected.</p>';
            }
        }

        // Handle "Apply Now" button click
        document.querySelectorAll('.btn-apply-job').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const jobId = btn.dataset.jobId || '';
                const jobTitle = btn.dataset.jobTitle || '';
                const company = btn.dataset.company || '';

                // Reset form
                document.getElementById('modalFullName').value = `{{ auth()->user()->name ?? '' }}`;
                document.getElementById('modalEmail').value = `{{ auth()->user()->email ?? '' }}`;
                document.getElementById('modalPhone').value = '';
                document.getElementById('modalLocation').value = '';
                document.getElementById('modalCover').value = '';
                document.getElementById('modalResumeFile').value = '';

                // Populate job info
                document.getElementById('modalJobTitle').textContent = jobTitle;

                // Set dataset for submission
                applicationModalEl.dataset.currentJobId = jobId;
                applicationModalEl.dataset.currentApplicationId = ''; // new application
                applicationModalEl.dataset.currentSavedResume = userSavedResumePath;

                // Show/hide checkbox if user has saved resume
                const useSavedWrap = document.getElementById('useSavedResumeWrap');
                const useSavedCheckbox = document.getElementById('useSavedResumeCheckbox');
                const modalSavedFilenameWrap = document.getElementById('modalSavedFilenameWrap');
                const modalSavedFilename = document.getElementById('modalSavedFilename');

                if (userSavedResumePath) {
                    useSavedWrap.style.display = 'block';
                    modalSavedFilenameWrap.style.display = 'block';
                    const filename = userSavedResumePath.split('/').pop();
                    modalSavedFilename.textContent = filename;
                    useSavedCheckbox.checked = true; // Default to using saved resume
                } else {
                    useSavedWrap.style.display = 'none';
                    modalSavedFilenameWrap.style.display = 'none';
                    useSavedCheckbox.checked = false;
                }

                // Update file input state
                updateResumeFileState(useSavedCheckbox.checked);
                updateResumePreview();

                applicationModal.show();
            });
        });

        // Handle checkbox change
        const useSavedResumeCheckbox = document.getElementById('useSavedResumeCheckbox');
        if (useSavedResumeCheckbox) {
            useSavedResumeCheckbox.addEventListener('change', function() {
                updateResumeFileState(this.checked);
                updateResumePreview();
            });
        }

        // Handle file input change
        const modalResumeFile = document.getElementById('modalResumeFile');
        if (modalResumeFile) {
            modalResumeFile.addEventListener('change', function() {
                updateResumePreview();
            });
        }

        // Handle SUBMIT APPLICATION button
        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        if (modalSubmitBtn) {
            modalSubmitBtn.addEventListener('click', async () => {
                const jobId = applicationModalEl.dataset.currentJobId || '';
                if (!jobId) {
                    alert('Missing job id.');
                    return;
                }

                const fullname = document.getElementById('modalFullName').value || '';
                const email = document.getElementById('modalEmail').value || '';
                const phone = document.getElementById('modalPhone').value || '';
                const location = document.getElementById('modalLocation').value || '';
                const cover = document.getElementById('modalCover').value || '';

                if (!cover || cover.trim() === '') {
                    alert('Cover letter is required.');
                    return;
                }

                const useSaved = document.getElementById('useSavedResumeCheckbox')?.checked || false;
                const fileInput = document.getElementById('modalResumeFile');
                const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;

                if (!useSaved && !hasFile) {
                    alert('Please upload a resume or check "Use my saved resume".');
                    return;
                }

                // Show processing state
                const originalText = modalSubmitBtn.textContent;
                modalSubmitBtn.textContent = 'Processing...';
                modalSubmitBtn.disabled = true;

                try {
                    const formData = new FormData();
                    formData.append('job_id', jobId);
                    formData.append('full_name', fullname);
                    formData.append('email', email);
                    formData.append('phone', phone);
                    formData.append('location', location);
                    formData.append('cover_letter', cover);

                    if (useSaved) {
                        formData.append('use_saved_resume', '1');
                    } else if (hasFile) {
                        formData.append('resume', fileInput.files[0]);
                    }

                    // Get CSRF token from meta tag or form
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

                    const resp = await fetch('/applications', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: formData,
                    });

                    if (resp.ok) {
                        try {
                            const data = await resp.json();
                            applicationModal.hide();
                            alert('Application submitted successfully!');
                            // Reload page to show updated dashboard
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } catch (e) {
                            // Response was ok but not JSON, likely a redirect
                            applicationModal.hide();
                            alert('Application submitted successfully!');
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        }
                    } else {
                        const errorText = await resp.text();
                        console.error('Submit failed:', resp.status, errorText);
                        console.log('Response headers:', resp.headers);
                        alert(`Failed to submit application (${resp.status}). Check console for details.`);
                    }
                } catch (err) {
                    console.error('Fetch error:', err);
                    alert('Error while submitting application: ' + err.message);
                } finally {
                    modalSubmitBtn.textContent = originalText;
                    modalSubmitBtn.disabled = false;
                }
            });
        }
    </script>
    <script src="{{ asset('legacy/js/common.js') }}"></script>
    <script src="{{ asset('legacy/js/jobs.js') }}"></script>
</body>
</html>