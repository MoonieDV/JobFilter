<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>JobFilter - Login</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('legacy/Images/log.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('legacy/css/login.css') }}" rel="stylesheet">
</head>
<body>
    <div class="bg-overlay"></div>

    <main class="login-wrapper">
        <img src="{{ asset('legacy/Images/logos.png') }}" alt="Brand Logo" class="brand-banner">

        <div class="card login-card">
            <div class="card-header border-0 pt-4 pb-0 text-center">
                <h5 class="mb-0">Login</h5>
                <p class="text-muted mt-2 mb-0">Welcome back! Sign in to continue.</p>
            </div>
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success py-2 mb-3 small" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div id="loginError" class="alert alert-danger py-2 mb-3 small" role="alert">
                        {{ $errors->first() }}
                    </div>
                @else
                    <div id="loginError" class="alert alert-danger d-none py-2 mb-3 small" role="alert">
                        Invalid credentials. Try <code>jobseeker@demo.com</code> with password <code>password123</code>.
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate autocomplete="on">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}"
                               placeholder="you@example.com" required autofocus>
                        <div class="form-text">We'll never share your information.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="Enter your password" required minlength="6">
                            <span class="input-group-text" id="togglePassword" aria-label="Toggle password visibility">👁️</span>
                        </div>
                        <div class="form-text">Must be at least 6 characters.</div>
                    </div>

                    <div class="row align-items-center mb-3">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <a href="{{ route('password.request') }}" class="forgot-password">Forgot password?</a>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary btn-lg" type="submit">Login</button>
                    </div>

                    <div class="text-center mt-3">
                        <span class="text-muted">New here?</span>
                        <a href="{{ route('register') }}" class="registration">Create an account</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('legacy/js/common.js') }}"></script>
    <script src="{{ asset('legacy/js/login.js') }}"></script>
</body>
</html>

