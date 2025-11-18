<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>JobFilter</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('legacy/Images/log.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('legacy/css/landing.css') }}" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold fs-3 text-primary" href="#">
                <i class="fas fa-filter me-2"></i>JobFilter
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#testimonials">Testimonials</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                    <li class="nav-item ms-lg-3">
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">Sign In</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                    </li>
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
                        Advanced job search technology that matches you with the perfect opportunities.
                        Save time, find better jobs, and advance your career faster.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="btn btn-warning btn-lg px-4 py-3 fw-bold">
                            <i class="fas fa-rocket me-2"></i>Start Free Trial
                        </a>
                        <a href="#how-it-works" class="btn btn-outline-light btn-lg px-4 py-3">
                            <i class="fas fa-play me-2"></i>Watch Demo
                        </a>
                    </div>
                    <div class="mt-5">
                        <p class="text-white-50 mb-2">Trusted by leading companies worldwide</p>
                        <div class="d-flex align-items-center gap-4">
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
                                    <h6 class="fw-bold mb-2">Job Match Found!</h6>
                                    <p class="text-muted small mb-0">Senior Developer at TechCorp</p>
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
                <p class="lead text-muted">Powerful features designed to revolutionize your job search</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-brain fa-3x text-primary"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Powered Matching</h5>
                        <p class="text-muted">Our advanced Website analyzes your skills and preferences to find the perfect job matches.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-clock fa-3x text-success"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Save Time</h5>
                        <p class="text-muted">Automated job applications and smart filtering save you hours every week.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-chart-line fa-3x text-warning"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Career Growth</h5>
                        <p class="text-muted">Track your progress and discover opportunities that align with your career goals.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-shield-alt fa-3x text-info"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Privacy First</h5>
                        <p class="text-muted">Your data is protected with enterprise-grade security and privacy controls.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">How JobFilter Works</h2>
                <p class="lead text-muted">Get started in just 3 simple steps</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="step-card">
                        <div class="step-number mb-3">1</div>
                        <h5 class="fw-bold mb-3">Create Your Profile</h5>
                        <p class="text-muted">Upload your resume and tell us about your skills, experience, and career goals.</p>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="step-card">
                        <div class="step-number mb-3">2</div>
                        <h5 class="fw-bold mb-3">Matches Jobs</h5>
                        <p class="text-muted">Our Website analyzes thousands of job postings to find the best matches for you.</p>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="step-card">
                        <div class="step-number mb-3">3</div>
                        <h5 class="fw-bold mb-3">Apply & Get Hired</h5>
                        <p class="text-muted">Apply to matched jobs with one click and track your application status.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonials" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">What Our Users Say</h2>
                <p class="lead text-muted">Join thousands of satisfied job seekers</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="testimonial-card">
                        <div class="testimonial-content p-4">
                            <div class="stars mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="mb-3">"JobFilter helped me find my dream job in just 2 weeks! The matching is incredible."</p>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">SM</div>
                                <div>
                                    <h6 class="fw-bold mb-0">Sarah Mitchell</h6>
                                    <small class="text-muted">Software Engineer at Google</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="testimonial-card">
                        <div class="testimonial-content p-4">
                            <div class="stars mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="mb-3">"I was spending hours searching for jobs. JobFilter cut that time by 90% and found better opportunities."</p>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">MJ</div>
                                <div>
                                    <h6 class="fw-bold mb-0">Mike Johnson</h6>
                                    <small class="text-muted">Marketing Manager at Apple</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="testimonial-card">
                        <div class="testimonial-content p-4">
                            <div class="stars mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="mb-3">"The career growth tracking feature helped me understand my market value and negotiate better salaries."</p>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">LD</div>
                                <div>
                                    <h6 class="fw-bold mb-0">Lisa Davis</h6>
                                    <small class="text-muted">Product Manager at Microsoft</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Ready to Transform Your Job Search?</h2>
            <p class="lead mb-5">Join over 100,000 professionals who have already found their dream jobs with JobFilter</p>
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <a href="{{ route('register') }}" class="btn btn-warning btn-lg px-5 py-3 fw-bold">
                    <i class="fas fa-rocket me-2"></i>Start Free Trial
                </a>
            </div>
            <p class="mt-4 text-white-50">No credit card required • 14-day free trial • Cancel anytime</p>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('legacy/js/landing.js') }}"></script>
</body>
</html>

