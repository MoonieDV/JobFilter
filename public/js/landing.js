document.addEventListener('DOMContentLoaded', () => {
    const observer = new IntersectionObserver(
        entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15 }
    );

    document.querySelectorAll('.feature-card, .testimonial-card, .step-card').forEach(el => {
        el.classList.add('fade-in');
        observer.observe(el);
    });
});

