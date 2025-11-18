// Role-based access control for JobFilter
document.addEventListener('DOMContentLoaded', function() {
    // Get user role from PHP session data
    let userRole = document.body.getAttribute('data-user-role') || 'job_seeker';
    
    // Fix role mapping for existing sessions
    if (userRole === 'employee') {
        userRole = 'job_seeker';
        console.log('Fixed role mapping: employee -> job_seeker');
    }
    
    // Define role permissions
    const rolePermissions = {
        job_seeker: ['dashboard.php', 'jobs.php', 'about.php', 'contact.php', 'user-profile.php'],
        employer: ['dashboard.php', 'post-job.php', 'about.php', 'contact.php', 'user-profile.php'],
        admin: ['*'] // Admin has access to everything
    };
    
    // Hide/show navigation items based on role
    function updateNavigation() {
        const allowedPages = rolePermissions[userRole] || [];
        const navLinks = document.querySelectorAll('#navMenuList .nav-link, #navMenu .nav-link');
        
        console.log('Updating navigation for role:', userRole);
        console.log('Allowed pages:', allowedPages);
        
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && !href.startsWith('#') && !href.startsWith('http')) {
                const fileName = href.split('?')[0].split('#')[0];
                const li = link.closest('li');
                
                if (li) {
                    const isAllowed = allowedPages.includes('*') || allowedPages.includes(fileName);
                    console.log(`Link ${fileName}: ${isAllowed ? 'ALLOWED' : 'BLOCKED'}`);
                    
                    if (isAllowed) {
                        li.style.display = '';
                        li.style.visibility = 'visible';
                    } else {
                        li.style.display = 'none';
                        li.style.visibility = 'hidden';
                    }
                }
            }
        });
    }
    
    // Check if current page is allowed for user role
    function checkPageAccess() {
        const currentPath = window.location.pathname;
        const currentFile = currentPath.split('/').pop();
        const allowedPages = rolePermissions[userRole] || [];
        const isAllowed = allowedPages.includes('*') || allowedPages.includes(currentFile);
        
        // Public pages that don't require role checks (including Laravel routes)
        const publicPages = ['login.php', 'Registration.php', 'landing.php', 'about.php', 'contact.php', 'index.php', 'dashboard.php'];
        const publicRoutes = ['/', '/about', '/contact', '/dashboard', '/profile', '/jobs', '/employer/jobs', '/applications', '/resumes'];
        
        // Check if current path matches any public route
        const isPublicRoute = publicRoutes.some(route => currentPath === route || currentPath.startsWith(route + '/'));
        
        // Allow access to dashboard and profile for all authenticated users
        if (currentFile === 'dashboard.php' || currentPath === '/dashboard' || currentPath === '/profile' || isPublicRoute) {
            return; // Allow access
        }
        
        // Skip check for Laravel routes (they have their own middleware)
        if (!currentFile.includes('.php') && currentPath.startsWith('/')) {
            return; // Laravel handles route authorization via middleware
        }
        
        if (!publicPages.includes(currentFile) && !isAllowed) {
            alert('Access denied for your role. Please sign in with the correct account.');
            window.location.href = '/login';
        }
    }
    
    // Update navigation on page load
    updateNavigation();
    
    // Check page access (but allow dashboard for all authenticated users)
    checkPageAccess();
    
    // Add role indicator to page
    function addRoleIndicator() {
        const roleIndicator = document.createElement('div');
        roleIndicator.className = 'position-fixed';
        roleIndicator.style.cssText = 'top: 10px; right: 10px; z-index: 1000; background: rgba(0,0,0,0.8); color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;';
        roleIndicator.textContent = `Role: ${userRole}`;
        document.body.appendChild(roleIndicator);
    }
    
    // Add role indicator (optional - remove in production)
    // addRoleIndicator();
});
