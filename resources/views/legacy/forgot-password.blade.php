<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('legacy/Images/log.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('legacy/css/forgot-password.css') }}" rel="stylesheet">
</head>
<body>
    <div class="bg-overlay"></div>

    <main class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-8 col-md-6 col-lg-5">
                <div class="card shadow-sm forgot-password-card">
                    <div class="card-header border-0 pt-4 pb-0">
                        <div class="d-flex align-items-center justify-content-center">
                            <img src="{{ asset('legacy/Images/log.png') }}" alt="Brand Logo" class="brand-logo">
                            <h5 class="mb-0">Forgot Password</h5>
                        </div>
                        <p class="text-muted mb-0 mt-2">Enter your email to reset your password.</p>

                        @if (session('status'))
                            <div class="alert alert-success py-2 mb-3 small" role="alert">
                                {{ session('status') }}
                            </div>
                        @elseif ($errors->any())
                            <div class="alert alert-danger py-2 mb-3 small" role="alert">
                                {{ $errors->first('email') }}
                            </div>
                        @endif

                        <form class="needs-validation" novalidate method="POST" action="{{ route('password.email') }}" autocomplete="on">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input
                                    type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    placeholder="Enter your email address"
                                    value="{{ old('email') }}"
                                    required
                                >
                                <div class="form-text">We'll send a password reset link to this email.</div>
                            </div>

                            <div class="d-grid">
                                <button class="btn btn-primary btn-lg" type="submit">Send Reset Link</button>
                            </div>

                            <div class="text-center mt-3">
                                <span class="text-muted">Remembered your password?</span>
                                <a href="{{ route('login') }}" class="back">← Back to Login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('legacy/js/forgot-password.js') }}"></script>
</body>
</html>

