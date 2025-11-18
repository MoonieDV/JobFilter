const links = document.querySelectorAll('.sidebar a[data-section]');
const sections = ['dashboard', 'users', 'employers', 'jobs', 'applications', 'feedbacks', 'settings'];

const burgerBtn = document.getElementById('burger-btn');
let lastActiveSection = 'dashboard';

let chartsInitialized = false;
let userGrowthChart, applicationsChart;

// Fade helpers with display toggle for better animation
function fadeOutSection(sectionEl) {
  if (!sectionEl) return;
  sectionEl.style.opacity = '0';
  sectionEl.style.transform = 'translateY(10px)';
  setTimeout(() => {
    sectionEl.classList.add('d-none');
    sectionEl.style.position = 'absolute';
    sectionEl.style.display = 'none';  // Hide completely for performance
  }, 400);
}

function fadeInSection(sectionEl) {
  if (!sectionEl) return;
  sectionEl.classList.remove('d-none');
  sectionEl.style.display = 'block';  // Show before animating
  setTimeout(() => {
    sectionEl.style.opacity = '1';
    sectionEl.style.transform = 'translateY(0)';
    sectionEl.style.position = 'relative';
  }, 50);
}

// Hide all sections immediately (no animation) - called on page load
function hideAllSections() {
  sections.forEach(section => {
    const sectionEl = document.getElementById(section + '-section');
    if (sectionEl) {
      sectionEl.classList.add('d-none');
      sectionEl.style.opacity = '0';
      sectionEl.style.transform = 'translateY(10px)';
      sectionEl.style.position = 'absolute';
      sectionEl.style.display = 'none';
    }
  });
}

// Update header title with capitalized first letter
function updateHeaderTitle(section) {
  const titleEl = document.getElementById('section-title');
  if (titleEl) {
    titleEl.textContent = section ? section.charAt(0).toUpperCase() + section.slice(1) : '';
  }
}

// Initialize Charts (only once)
function initCharts() {
  if (chartsInitialized) return;

  // User Growth Chart - Line
  const ctxUserGrowth = document.getElementById('userGrowthChart').getContext('2d');
  userGrowthChart = new Chart(ctxUserGrowth, {
    type: 'line',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
      datasets: [{
        label: 'Users',
        data: [100, 300, 500, 700, 900, 1100, 1300, 1500, 1700],
        borderColor: '#0d6efd',
        backgroundColor: 'rgba(13, 110, 253, 0.2)',
        fill: true,
        tension: 0.3,
        pointRadius: 4,
        pointHoverRadius: 6,
      }]
    },
    options: {
      responsive: true,
      animation: {
        duration: 1000,
        easing: 'easeOutQuart'
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { stepSize: 200 }
        }
      },
      plugins: {
        legend: { display: false }
      }
    }
  });

  // Applications by Category Chart - Doughnut
  const ctxApplications = document.getElementById('applicationsChart').getContext('2d');
  applicationsChart = new Chart(ctxApplications, {
    type: 'doughnut',
    data: {
      labels: ['IT', 'Marketing', 'Design', 'Finance', 'Sales'],
      datasets: [{
        label: 'Applications',
        data: [500, 300, 250, 700, 400],
        backgroundColor: [
          '#0d6efd',
          '#198754',
          '#ffc107',
          '#dc3545',
          '#6c757d'
        ],
        hoverOffset: 30
      }]
    },
    options: {
      responsive: true,
      animation: {
        duration: 1000,
        easing: 'easeOutQuart'
      },
      plugins: {
        legend: { position: 'bottom' }
      }
    }
  });

  chartsInitialized = true;
}

// Show target section and hide others
function showSection(target) {
  sections.forEach(section => {
    const sectionEl = document.getElementById(section + '-section');
    if (section === target) {
      fadeInSection(sectionEl);
      if (section === 'dashboard') {
        initCharts(); // Initialize charts when dashboard is shown
      }
    } else {
      fadeOutSection(sectionEl);
    }
  });
}

// Nav click
links.forEach(link => {
  link.addEventListener('click', (e) => {
    e.preventDefault();
    const target = link.getAttribute('data-section');
    if (!target) return;

    lastActiveSection = target;

    links.forEach(l => l.classList.remove('active'));
    link.classList.add('active');

    updateHeaderTitle(target);
    showSection(target);

    // On small screens, collapse sidebar after click
    if (window.innerWidth <= 768 && !document.body.classList.contains('sidebar-collapsed')) {
      document.body.classList.add('sidebar-collapsed');
      burgerBtn.setAttribute('aria-expanded', 'false');
      burgerBtn.classList.remove('open');
      document.body.classList.remove('sidebar-open');
    }
  });
});

// Burger toggle
if (burgerBtn) {
  burgerBtn.addEventListener('click', () => {
    const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
    
    if (isCollapsed) {
      // Sidebar is now collapsed (hidden)
      burgerBtn.setAttribute('aria-expanded', 'false');
      burgerBtn.classList.remove('open');
      document.body.classList.remove('sidebar-open');
    } else {
      // Sidebar is now open (visible)
      burgerBtn.setAttribute('aria-expanded', 'true');
      burgerBtn.classList.add('open');
      document.body.classList.add('sidebar-open');
    }
  });
}

// Admin Settings load/save
function loadSettings() {
  const savedSettings = JSON.parse(localStorage.getItem('adminSettings'));
  if (!savedSettings) return;

  document.getElementById('siteName').value = savedSettings.siteName || '';
  document.getElementById('language').value = savedSettings.language || 'en';
  document.getElementById('enableRegistration').checked = savedSettings.enableRegistration ?? true;
  document.getElementById('enableEmailNotifications').checked = savedSettings.enableEmailNotifications ?? true;
}

document.getElementById('settings-form')?.addEventListener('submit', function(e) {
  e.preventDefault();

  const settings = {
    siteName: document.getElementById('siteName').value.trim(),
    language: document.getElementById('language').value,
    enableRegistration: document.getElementById('enableRegistration').checked,
    enableEmailNotifications: document.getElementById('enableEmailNotifications').checked,
  };

  localStorage.setItem('adminSettings', JSON.stringify(settings));

  const message = document.getElementById('settings-message');
  message.textContent = 'Settings saved successfully!';
  message.className = 'text-success';

  setTimeout(() => {
    message.textContent = '';
  }, 3000);
});

// On page load
window.addEventListener('DOMContentLoaded', () => {
  document.body.classList.add('sidebar-collapsed');
burgerBtn.setAttribute('aria-expanded', 'false');
burgerBtn.classList.remove('open');
document.body.classList.remove('sidebar-open');

  hideAllSections(); // Hide all sections immediately on load
  updateHeaderTitle(lastActiveSection);
  showSection(lastActiveSection);
  loadSettings();

  // Initialize burger button state
  if (!document.body.classList.contains('sidebar-collapsed')) {
    burgerBtn.classList.add('open');
    document.body.classList.add('sidebar-open');
    burgerBtn.setAttribute('aria-expanded', 'true');
  } else {
    burgerBtn.classList.remove('open');
    document.body.classList.remove('sidebar-open');
    burgerBtn.setAttribute('aria-expanded', 'false');
  }
});
