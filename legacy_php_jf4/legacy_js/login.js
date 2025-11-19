// Login page UI functionality
(function () {
    'use strict';

    // Password visibility toggle
    const pwd = document.getElementById('password');
    const toggle = document.getElementById('togglePassword');
    if (toggle && pwd) {
        toggle.addEventListener('click', function () {
            const isHidden = pwd.getAttribute('type') === 'password';
            pwd.setAttribute('type', isHidden ? 'text' : 'password');
            toggle.textContent = isHidden ? '🙈' : '👁️';
        });
    }

    // Form validation feedback
    const form = document.querySelector('form.needs-validation');
    if (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            // Always add validation class for styling
            form.classList.add('was-validated');
            // Let valid forms submit naturally to PHP
        }, false);
    }

    // Show/hide error messages with animation
    function showError(message) {
        const errorBox = document.getElementById('loginError');
        if (errorBox) {
            errorBox.textContent = message;
            errorBox.classList.remove('d-none');
            errorBox.style.opacity = '0';
            errorBox.style.transform = 'translateY(-10px)';
            
            // Animate in
            setTimeout(() => {
                errorBox.style.transition = 'all 0.3s ease';
                errorBox.style.opacity = '1';
                errorBox.style.transform = 'translateY(0)';
            }, 10);
        }
    }

    function hideError() {
        const errorBox = document.getElementById('loginError');
        if (errorBox) {
            errorBox.style.transition = 'all 0.3s ease';
            errorBox.style.opacity = '0';
            errorBox.style.transform = 'translateY(-10px)';
            
            setTimeout(() => {
                errorBox.classList.add('d-none');
            }, 300);
        }
    }

    // Make functions globally available for PHP error handling
    window.showLoginError = showError;
    window.hideLoginError = hideError;
})();