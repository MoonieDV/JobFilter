@php
    use Illuminate\Support\Str;
    $user = auth()->user();
    $role = $user->role ?? 'guest';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Us - JobFilter</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('legacy/Images/log.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('legacy/css/home.css') }}" rel="stylesheet">
    <link href="{{ asset('legacy/css/contact.css') }}" rel="stylesheet">
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
                        <h5 class="fw-bold mb-2" id="offcanvasName">{{ $user->name ?? 'Guest' }}</h5>
                        <p class="text-muted small mb-2">{{ $user->email ?? '' }}</p>
                        <p class="text-muted small mb-2">Role: {{ Str::headline($role) }}</p>
                        @auth
                            <form method="POST" action="{{ route('logout') }}" class="needs-logout-confirm">
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
                        <li class="nav-item"><a class="nav-link" href="{{ route('jobs.browse') }}">Find Jobs</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('employer.jobs.index') }}">Post Job</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('legacy.about') }}">About</a></li>
                        <li class="nav-item"><a class="nav-link active" href="{{ route('legacy.contact') }}">Contact</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <section class="py-5 text-center bg-light">
        <div class="container">
            <h1 class="fw-bold mb-2">Get in Touch</h1>
            <p class="text-muted mb-0">Questions, feedback, or partnership ideas? We'd love to hear from you.</p>
        </div>
    </section>

    <main>
        <section class="py-5">
            <div class="container">
                <div class="row g-4">
                    <div class="col-12 col-lg-7">
                        <div class="card shadow-sm h-100">
                            <div class="card-header"><h5 class="mb-0">Send us a message</h5></div>
                            <div class="card-body">
                                @if (session('status'))
                                    <div class="alert alert-success" role="alert">
                                        {{ session('status') }}
                                    </div>
                                @endif
                                <form id="contactForm" class="needs-validation" novalidate action="{{ route('contact.store') }}" method="POST">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <label for="name" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="name" name="name" placeholder="Your name" value="{{ old('name', $user->name ?? '') }}" required>
                                            <div class="invalid-feedback">Please enter your name.</div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" value="{{ old('email', $user->email ?? '') }}" required>
                                            <div class="invalid-feedback">Please enter a valid email.</div>
                                        </div>
                                        <div class="col-12">
                                            <label for="subject" class="form-label">Subject</label>
                                            <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject" value="{{ old('subject') }}" required>
                                            <div class="invalid-feedback">Please add a subject.</div>
                                        </div>
                                        <div class="col-12">
                                            <label for="message" class="form-label">Message</label>
                                            <textarea class="form-control" id="message" name="message" rows="6" placeholder="How can we help?" required>{{ old('message') }}</textarea>
                                            <div class="invalid-feedback">Please enter a message.</div>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-primary">Send Message</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-5">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header"><h6 class="mb-0">Contact Information</h6></div>
                            <div class="card-body">
                                <p class="mb-2"><strong>Email:</strong> support@jobfilter.example</p>
                                <p class="mb-2"><strong>Phone:</strong> +1 (555) 123-4567</p>
                                <p class="mb-0"><strong>Address:</strong> 123 Market Street, Suite 200, Manila, Philippines</p>
                            </div>
                        </div>
                        <div class="card shadow-sm h-100">
                            <div class="card-header"><h6 class="mb-0">Office Hours</h6></div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    <li>Mon–Fri: 9:00 AM – 6:00 PM</li>
                                    <li>Sat: 10:00 AM – 2:00 PM</li>
                                    <li>Sun: Closed</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

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
    <script src="{{ asset('legacy/js/contact.js') }}"></script>
</body>
</html>

