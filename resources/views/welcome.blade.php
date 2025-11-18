<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'JobFilter') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/log.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/landing.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3 text-primary" href="#">
                <i class="fas fa-filter me-2"></i>{{ config('app.name', 'JobFilter') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto" id="navMenuList">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#testimonials">Testimonials</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                    @if (Route::has('login'))
                        @auth
                            <li class="nav-item ms-lg-3">
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">Dashboard</a>
                            </li>
                        @else
                            <li class="nav-item ms-lg-3">
                                <a href="{{ route('login') }}" class="btn btn-outline-primary">Sign In</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item ms-lg-2">
                                    <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                                </li>
                            @endif
                        @endauth
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-white mb-4">
                        Find Your Dream Job with <span class="text-warning">JobFilter</span>
                    </h1>
                    <p class="lead text-white-50 mb-5">
                        Advanced matching technology analyzes your skills and goals to connect you with the perfect opportunities.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-warning btn-lg px-4 py-3 fw-bold">
                                <i class="fas fa-rocket me-2"></i>Start Free Trial
                            </a>
                        @endif
                        <a href="#how-it-works" class="btn btn-outline-light btn-lg px-4 py-3">
                            <i class="fas fa-play me-2"></i>Watch Demo
                        </a>
                    </div>
                    <div class="mt-5">
                        <p class="text-white-50 mb-2">Trusted by hiring teams worldwide</p>
                        <div class="d-flex align-items-center gap-4 flex-wrap">
                            <div class="company-logo">Google</div>
                            <div class="company-logo">Microsoft</div>
                            <div class="company-logo">Apple</div>
                            <div class="company-logo">Amazon</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="hero-image">
                        <i class="fas fa-search fa-8x text-warning mb-3"></i>
                        <div class="floating-card">
                            <div class="card border-0 shadow-lg">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-2">Match Found!</h6>
                                    <p class="text-muted small mb-0">Senior Developer · TechCorp · 98% fit</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Why Choose JobFilter?</h2>
                <p class="lead text-muted">Powerful features designed for talent teams and job seekers</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-brain fa-3x text-primary"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Intelligent Matching</h5>
                        <p class="text-muted">AI analyzes resumes, skills, and preferences to surface curated job matches.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-clock fa-3x text-success"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Save Time</h5>
                        <p class="text-muted">Automated filtering, resume parsing, and one-click apply streamline hiring.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-chart-line fa-3x text-warning"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Career Growth</h5>
                        <p class="text-muted">Track application progress, interview feedback, and skills to improve outcomes.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-shield-alt fa-3x text-info"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Privacy First</h5>
                        <p class="text-muted">Enterprise-grade security keeps resumes, data, and decisions protected.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Get Hired in 3 Simple Steps</h2>
                <p class="lead text-muted">We guide you end-to-end—from profile to offer.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="step-card">
                        <div class="step-number mb-3">1</div>
                        <h5 class="fw-bold mb-3">Create Your Profile</h5>
                        <p class="text-muted">Upload your resume, portfolio, and preferred roles in minutes.</p>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="step-card">
                        <div class="step-number mb-3">2</div>
                        <h5 class="fw-bold mb-3">Discover Matched Jobs</h5>
                        <p class="text-muted">JobFilter evaluates thousands of listings and scores the best fits.</p>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="step-card">
                        <div class="step-number mb-3">3</div>
                        <h5 class="fw-bold mb-3">Apply & Track</h5>
                        <p class="text-muted">Apply instantly, collaborate with recruiters, and track progress in one dashboard.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonials" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Loved by candidates & employers</h2>
                <p class="lead text-muted">Here’s what our community says</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="testimonial-card p-4">
                        <div class="stars mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-3">“JobFilter aligned my skills with remote-first teams. I signed an offer in 14 days.”</p>
                        <div class="d-flex align-items-center">
                            <div class="avatar me-3">SM</div>
                            <div>
                                <h6 class="fw-bold mb-0">Sarah Mitchell</h6>
                                <small class="text-muted">Software Engineer · Google</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="testimonial-card p-4">
                        <div class="stars mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-3">“Our recruiting team saves hours per role thanks to automated scoring and alerts.”</p>
                        <div class="d-flex align-items-center">
                            <div class="avatar me-3">MJ</div>
                            <div>
                                <h6 class="fw-bold mb-0">Mike Johnson</h6>
                                <small class="text-muted">Talent Lead · Apple</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="testimonial-card p-4">
                        <div class="stars mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="mb-3">“Insightful analytics helped me benchmark salaries and negotiate with confidence.”</p>
                        <div class="d-flex align-items-center">
                            <div class="avatar me-3">LD</div>
                            <div>
                                <h6 class="fw-bold mb-0">Lisa Davis</h6>
                                <small class="text-muted">Product Manager · Microsoft</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="pricing" class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Ready to transform your job search?</h2>
            <p class="lead mb-5">Join thousands of candidates and hiring teams accelerating outcomes with JobFilter.</p>
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-warning btn-lg px-5 py-3 fw-bold">
                        <i class="fas fa-rocket me-2"></i>Start Free Trial
                    </a>
                @endif
                <a href="#contact" class="btn btn-outline-light btn-lg px-5 py-3 fw-bold">
                    Talk to Sales
                </a>
            </div>
            <p class="mt-4 text-white-50 mb-0">No credit card required · Cancel anytime</p>
        </div>
    </section>

    <footer class="footer py-4 text-white">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-filter me-2 text-warning"></i>{{ config('app.name', 'JobFilter') }}
                    </h5>
                    <p class="text-muted">AI-powered job filtering and matching for modern teams.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
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
            <hr class="my-4 border-secondary">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">&copy; {{ now()->year }} {{ config('app.name', 'JobFilter') }}. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-muted">Built with <i class="fas fa-heart text-danger"></i> for job seekers</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/landing.js') }}"></script>
</body>
</html>

