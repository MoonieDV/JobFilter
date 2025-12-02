<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>JobFilter - Registration</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('legacy/Images/log.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('legacy/css/registration.css') }}" rel="stylesheet">
</head>
<body>
    <div class="bg-overlay"></div>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="card-title text-center mb-4">Sign Up to JobFilter</h3>

                        <div class="mb-4">
                            <div class="d-flex justify-content-center gap-4">
                                <div id="employeeCard" class="role-card selected" onclick="selectRole('employee')">
                                    <i class="bi bi-person"></i>
                                    <div>Employee</div>
                                </div>
                                <div id="employerCard" class="role-card" onclick="selectRole('employer')">
                                    <i class="bi bi-building"></i>
                                    <div>Employer</div>
                                </div>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger py-2 mb-3 small" role="alert">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form id="registrationForm" enctype="multipart/form-data" method="POST" action="{{ route('register') }}" novalidate>
                            @csrf
                            <input type="hidden" id="role" name="role" value="{{ old('role', 'employee') }}">

                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" id="first_name" name="first_name"
                                               value="{{ old('first_name') }}" placeholder="First name" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" id="middle_name" name="middle_name"
                                               value="{{ old('middle_name') }}" placeholder="Middle name (optional)">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" id="last_name" name="last_name"
                                               value="{{ old('last_name') }}" placeholder="Last name" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="Enter email" required>
                            </div>

                            <div class="row g-3 align-items-center">
                                <div class="col-auto">
                                    <label for="password" class="col-form-label">Password</label>
                                </div>
                                <div class="col-auto flex-grow-1">
                                    <input type="password" id="password" name="password" class="form-control" placeholder="At least 6 characters" required>
                                </div>
                            </div>

                            <div id="employeeFields">
                                <div class="mb-3">
                                    <label for="jobTitle" class="form-label">Job Title</label>
                                    <input type="text" class="form-control" id="jobTitle" name="job_title" value="{{ old('job_title') }}" placeholder="e.g. Web Developer">
                                </div>
                                <div class="mb-3">
                                    <label for="employeePhone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="employeePhone" name="phone" value="{{ old('phone') }}" placeholder="Enter your phone number" pattern="^\+?[0-9\s\-]{7,15}$">
                                </div>
                                <div class="mb-3">
                                    <label for="dob" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="dob" name="dob" value="{{ old('dob') }}">
                                </div>
                                <div class="mb-3">
                                    <label for="bio" class="form-label">Short Bio</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Tell us a little about yourself">{{ old('bio') }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="resume" class="form-label">Upload Resume (PDF or DOCX)</label>
                                    <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                                    <div class="form-text">Supported formats: PDF, DOCX, DOC.</div>
                                </div>
                            </div>

                            <div id="employerFields" style="display: none;">
                                <div class="mb-3">
                                    <label for="companyName" class="form-label">Company Name</label>
                                    <input type="text" class="form-control" id="companyName" name="company_name" value="{{ old('company_name') }}" placeholder="Enter your company name">
                                </div>
                                <div class="mb-3">
                                    <label for="companyRegNumber" class="form-label">Company Registration Number</label>
                                    <input type="text" class="form-control" id="companyRegNumber" name="company_reg_number" value="{{ old('company_reg_number') }}" placeholder="Registration or tax ID">
                                </div>
                                <div class="mb-3">
                                    <label for="companyAddress" class="form-label">Company Address</label>
                                    <input type="text" class="form-control" id="companyAddress" name="company_address" value="{{ old('company_address') }}" placeholder="Enter company address">
                                </div>
                                <div class="mb-3">
                                    <label for="companyPhone" class="form-label">Company Phone Number</label>
                                    <input type="tel" class="form-control" id="companyPhone" name="company_phone" value="{{ old('company_phone') }}" placeholder="Enter company phone number" pattern="^\+?[0-9\s\-]{7,15}$">
                                </div>
                                <div class="mb-3">
                                    <label for="companyLinkedIn" class="form-label">LinkedIn Profile (Optional)</label>
                                    <input type="url" class="form-control" id="companyLinkedIn" name="company_linkedin" value="{{ old('company_linkedin') }}" placeholder="https://linkedin.com/company/your-company">
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Register</button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <small>
                                Already have an account?
                                <a href="{{ route('login') }}">Login</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('legacy/js/common.js') }}"></script>
    <script>
        window.selectedRole = '{{ old('role', 'employee') }}';
        document.addEventListener('DOMContentLoaded', function () {
            selectRole(window.selectedRole);
        });
    </script>
    <script src="{{ asset('legacy/js/registration.js') }}" defer></script>
</body>
</html>

