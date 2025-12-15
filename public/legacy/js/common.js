// Common JavaScript functionality for JobFilter
// No authentication logic - all handled by PHP

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (!alert.classList.contains('alert-danger')) {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 5000);
        }
    });

    // Confirm logout for legacy links and Laravel forms
    const confirmLogout = (event) => {
        const ok = window.confirm('Do you want to logout?');
        if (!ok) {
            event.preventDefault();
            event.stopPropagation();
        }
    };

    document.querySelectorAll('a[href="process_logout.php"]').forEach(link => {
      link.addEventListener('click', confirmLogout);
    });

    document.querySelectorAll('form[action*="logout"], form.needs-logout-confirm').forEach(form => {
        form.addEventListener('submit', confirmLogout);
    });

    // Add smooth animations to cards and buttons
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.style.transition = 'transform 0.2s ease, box-shadow 0.2s ease';
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });

    // Add loading states to buttons (but not for forms that submit to PHP)
    const buttons = document.querySelectorAll('button:not([type="submit"]), .btn-primary:not([type="submit"]), .btn-success:not([type="submit"])');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            if (!this.type || this.type !== 'submit') {
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                this.disabled = true;
                
                // Re-enable after 2 seconds
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 2000);
            }
        });
    });
});
