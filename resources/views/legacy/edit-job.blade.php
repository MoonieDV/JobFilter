@php
    use Illuminate\Support\Str;
    $user = auth()->user();
    $role = $user->role ?? 'job_seeker';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Job - JobFilter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('legacy/css/home.css') }}" rel="stylesheet">
    <link href="{{ asset('legacy/css/post-job.css') }}" rel="stylesheet">
</head>
<body data-user-role="{{ $role }}">
    <nav class="navbar navbar-light bg-light sticky-top shadow-sm navbar-glass">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">JobFilter</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#navMenu" aria-controls="navMenu" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="offcanvas offcanvas-end" tabindex="-1" id="navMenu" aria-labelledby="navMenuLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="navMenuLabel">Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <div class="text-center mb-3">
                        <img id="offcanvasAvatar" src="{{ asset('legacy/Images/log.png') }}" class="rounded-circle mb-2" width="72" height="72" alt="Avatar">
                        <h5 class="fw-bold mb-2">{{ $user->name }}</h5>
                        <p class="text-muted small mb-2">{{ $user->email }}</p>
                        <p class="text-muted small mb-2">Role: {{ Str::headline($role) }}</p>
                        <form method="POST" action="{{ route('logout') }}" class="needs-logout-confirm">
                            @csrf
                            <button class="btn btn-outline-danger btn-sm">Logout</button>
                        </form>
                        <hr class="mt-3">
                    </div>
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0" id="navMenuList">
                        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('jobs.browse') }}">Find Jobs</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('employer.jobs.index') }}">My Job Posts</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('legacy.about') }}">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('legacy.contact') }}">Contact</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10">
                    <div class="text-center mb-5">
                        <h1 class="fw-bold">Edit Job Posting</h1>
                        <p class="text-muted">Update your job listing details</p>
                    </div>

                    <div class="card shadow-lg">
                        <div class="card-body p-5">
                            @if (session('status'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('status') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form id="jobEditForm" class="needs-validation" novalidate method="POST" action="{{ route('employer.jobs.update', $job) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" id="jobStatus" value="{{ $job->status }}">

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h4 class="mb-3">Basic Information</h4>
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <label for="jobTitle" class="form-label">Job Title *</label>
                                        <input type="text" class="form-control" id="jobTitle" name="title" required value="{{ old('title', $job->title) }}">
                                        <div class="invalid-feedback">Please provide a job title.</div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <label for="companyName" class="form-label">Company Name *</label>
                                        <input type="text" class="form-control" id="companyName" name="company_name" required value="{{ old('company_name', $job->company_name) }}">
                                        <div class="invalid-feedback">Please provide your company name.</div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <label for="location" class="form-label">Location *</label>
                                        <input type="text" class="form-control" id="location" name="location" placeholder="City, State or Remote" required value="{{ old('location', $job->location) }}">
                                        <div class="invalid-feedback">Please provide a location.</div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <label for="jobCategory" class="form-label">Job Category *</label>
                                        <select class="form-select" id="jobCategory" required>
                                            <option value="">Select Category</option>
                                            <option value="technology">Technology</option>
                                            <option value="marketing">Marketing</option>
                                            <option value="sales">Sales</option>
                                            <option value="design">Design</option>
                                            <option value="finance">Finance</option>
                                            <option value="healthcare">Healthcare</option>
                                            <option value="education">Education</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a job category.</div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h4 class="mb-3">Job Details</h4>
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <label for="jobType" class="form-label">Job Type *</label>
                                        <select class="form-select" id="jobType" name="employment_type" required>
                                            <option value="">Select Job Type</option>
                                            <option value="full_time" @selected(old('employment_type', $job->employment_type)==='full_time')>Full Time</option>
                                            <option value="part_time" @selected(old('employment_type', $job->employment_type)==='part_time')>Part Time</option>
                                            <option value="contract" @selected(old('employment_type', $job->employment_type)==='contract')>Contract</option>
                                            <option value="internship" @selected(old('employment_type', $job->employment_type)==='internship')>Internship</option>
                                            <option value="remote" @selected(old('employment_type', $job->employment_type)==='remote')>Remote</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a job type.</div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <label for="experienceLevel" class="form-label">Experience Level *</label>
                                        <select class="form-select" id="experienceLevel" name="experience_level" required>
                                            <option value="">Select Experience Level</option>
                                            <option value="entry" @selected(old('experience_level', $job->experience_level)==='entry')>Entry Level (0-2 years)</option>
                                            <option value="mid" @selected(old('experience_level', $job->experience_level)==='mid')>Mid Level (3-5 years)</option>
                                            <option value="senior" @selected(old('experience_level', $job->experience_level)==='senior')>Senior Level (5+ years)</option>
                                            <option value="executive" @selected(old('experience_level', $job->experience_level)==='executive')>Executive Level</option>
                                        </select>
                                        <div class="invalid-feedback">Please select an experience level.</div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <label for="salary" class="form-label">Salary (Optional)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="salary" name="salary" placeholder="80000" value="{{ old('salary', $job->salary) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h4 class="mb-3">Skills & Requirements</h4>
                                        <p class="text-muted">Add skills that are required for this position. Our AI will use these to match candidates.</p>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="requiredSkills" class="form-label">Required Skills *</label>
                                        <input type="text" class="form-control" id="requiredSkills" name="required_skills" placeholder="e.g., JavaScript, React, Node.js, SQL" required value="{{ old('required_skills', isset($job->required_skills) ? implode(', ', $job->required_skills) : '') }}">
                                        <div class="form-text">Separate skills with commas</div>
                                        <div class="invalid-feedback">Please provide required skills.</div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="preferredSkills" class="form-label">Preferred Skills (Optional)</label>
                                        <input type="text" class="form-control" id="preferredSkills" name="preferred_skills" placeholder="e.g., TypeScript, AWS, Docker" value="{{ old('preferred_skills', isset($job->preferred_skills) ? implode(', ', $job->preferred_skills) : '') }}">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="jobDescription" class="form-label">Job Description *</label>
                                        <textarea class="form-control" id="jobDescription" name="description" rows="6" placeholder="Describe the role, responsibilities, and what you're looking for..." required>{{ old('description', $job->description) }}</textarea>
                                        <div class="invalid-feedback">Please provide a job description.</div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h4 class="mb-3">Company Information</h4>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="companyDescription" class="form-label">Company Description</label>
                                        <textarea class="form-control" id="companyDescription" name="responsibilities" rows="3" placeholder="Tell candidates about your company culture...">{{ old('responsibilities', $job->responsibilities) }}</textarea>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="contactEmail" class="form-label">Contact Email *</label>
                                        <input type="email" class="form-control" id="contactEmail" name="requirements" placeholder="hr@company.com" value="{{ old('requirements', $job->requirements) }}" required>
                                        <div class="invalid-feedback">Please provide a valid email address.</div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h4 class="mb-3">Job Status</h4>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="jobStatus" class="form-label">Status *</label>
                                        <select class="form-select" id="jobStatusSelect" name="status" required>
                                            <option value="open" @selected(old('status', $job->status)==='open')>Open</option>
                                            <option value="draft" @selected(old('status', $job->status)==='draft')>Draft</option>
                                            <option value="closed" @selected(old('status', $job->status)==='closed')>Closed</option>
                                        </select>
                                        <div class="form-text">Open jobs are visible to job seekers</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 text-center">
                                        <a href="{{ route('employer.jobs.index') }}" class="btn btn-outline-secondary me-3">Cancel</a>
                                        <button type="submit" class="btn btn-primary btn-lg" id="updateJobBtn">Update Job</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer py-4">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <p class="mb-0 text-muted">&copy; 2025 JobFilter. All rights reserved.</p>
            <div class="d-flex flex-wrap gap-3">
                <a href="#" class="text-decoration-none text-muted">Privacy</a>
                <a href="#" class="text-decoration-none text-muted">Terms</a>
                <a href="#" class="text-decoration-none text-muted">Support</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('legacy/js/common.js') }}"></script>
    <script>
        (function () {
            const form = document.getElementById('jobEditForm');
            const statusSelect = document.getElementById('jobStatusSelect');

            form.addEventListener('submit', () => {
                document.getElementById('jobStatus').value = statusSelect.value;
            });
        })();
    </script>
</body>
</html>
