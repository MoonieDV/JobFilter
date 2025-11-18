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
    <title>About Us - JobFilter</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('legacy/Images/log.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('legacy/css/about.css') }}" rel="stylesheet">
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
                        <li class="nav-item"><a class="nav-link active" href="{{ route('legacy.about') }}">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('legacy.contact') }}">Contact</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <section class="py-5 text-center bg-light">
        <div class="container">
            <h1 class="fw-bold mb-3">About JobFilter</h1>
            <p class="text-muted mb-0">Revolutionizing job matching through intelligent skill-based algorithms</p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-12 col-lg-6">
                    <h2 class="fw-bold mb-3">Our Mission</h2>
                    <p class="text-muted mb-4">At JobFilter, we believe that finding the right job or the perfect candidate shouldn't be a time-consuming process. Our platform leverages advanced skill-matching algorithms to connect talented professionals with opportunities that truly match their expertise and career goals.</p>
                    <p class="text-muted mb-0">We're committed to reducing recruitment time, improving job matching efficiency, and helping both job seekers and employers achieve their goals faster and more effectively.</p>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="display-4 text-primary mb-3">🎯</div>
                            <h5 class="fw-bold">Smart Matching</h5>
                            <p class="text-muted mb-0">Our AI-powered system analyzes skills, experience, and preferences to deliver the most relevant matches.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Our Values</h2>
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <div class="display-6 text-primary mb-3">⚡</div>
                            <h5 class="fw-bold">Efficiency</h5>
                            <p class="text-muted mb-0">Streamlining the recruitment process to save time for both employers and job seekers.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <div class="display-6 text-primary mb-3">🎯</div>
                            <h5 class="fw-bold">Accuracy</h5>
                            <p class="text-muted mb-0">Providing precise skill-based matches to ensure the best possible connections.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <div class="display-6 text-primary mb-3">🤝</div>
                            <h5 class="fw-bold">Transparency</h5>
                            <p class="text-muted mb-0">Clear and honest communication throughout the entire matching process.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Our Team</h2>
            <div class="row g-4">
                @foreach ([
                    ['name' => 'Christian Crisostomo', 'role' => 'Project Manager', 'image' => 'Christian Crisostomo.jpg', 'modal' => 'Christian'],
                    ['name' => 'Alyssa Jane Prak', 'role' => 'System Analyst/Database Designer', 'image' => 'Alyssa jane prak.jpg', 'modal' => 'Alyssa'],
                    ['name' => 'Frank Oliver Bentoy', 'role' => 'Software Engineer', 'image' => 'Frank Oliver Bentoy..jpg', 'modal' => 'Frank'],
                    ['name' => 'Shine Florence Padillo', 'role' => 'Software Tester/Technical Writer', 'image' => 'Shine Florence Padillo.jpg', 'modal' => 'Shine'],
                ] as $member)
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card shadow-sm text-center">
                            <div class="card-body p-4">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#resume{{ $member['modal'] }}">
                                    <img src="{{ asset('legacy/Images/'.$member['image']) }}" alt="{{ $member['name'] }}" class="rounded-circle mb-3" width="120" height="120">
                                </a>
                                <h5 class="fw-bold mb-1">{{ $member['name'] }}</h5>
                                <p class="text-muted mb-0">{{ $member['role'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @include('legacy.partials.team-modals')

    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-12 col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <div class="display-4 fw-bold text-primary mb-2">10K+</div>
                            <p class="text-muted mb-0">Active Users</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <div class="display-4 fw-bold text-primary mb-2">5K+</div>
                            <p class="text-muted mb-0">Jobs Posted</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <div class="display-4 fw-bold text-primary mb-2">95%</div>
                            <p class="text-muted mb-0">Match Accuracy</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <div class="display-4 fw-bold text-primary mb-2">24/7</div>
                            <p class="text-muted mb-0">Support Available</p>
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
    <script src="{{ asset('legacy/js/about.js') }}"></script>
</body>
</html>

