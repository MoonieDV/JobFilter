// Jobs page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize job listings and sorting
    initializeJobs();
    
    // Set up event listeners
    setupEventListeners();
});

function initializeJobs() {
    // Add sort order indicator to salary option
    const sortSelect = document.getElementById('sortBy');
    if (sortSelect) {
        const salaryOption = sortSelect.querySelector('option[value="salary"]');
        if (salaryOption) {
            salaryOption.textContent = 'Sort by Salary (High to Low)';
        }
    }

    // Apply Now buttons
    document.addEventListener('click', async function(e) {
        const btn = e.target.closest('.apply-btn');
        if (!btn) return;
        e.preventDefault();
        if (btn.dataset.applied === '1') {
            showNotification('You already applied to this job.', 'info');
            return;
        }

        const jobId = parseInt(btn.getAttribute('data-job-id'));
        const title = btn.getAttribute('data-job-title') || 'Job';
        const company = btn.getAttribute('data-company') || '';
        const originalText = btn.textContent;
        btn.textContent = 'Applying...';
        try {
            const formData = new FormData();
            formData.append('job_id', jobId);
            const res = await fetch('process_apply.php', { method: 'POST', body: formData });
            if (!res.ok) {
                throw new Error('Failed to apply');
            }
            // Try parse JSON; if HTML came back, treat as error
            const text = await res.text();
            let data;
            try { data = JSON.parse(text); } catch (_) { throw new Error('Unexpected response'); }
            if (!data.success) throw new Error(data.message || 'Failed to apply');

            // Update all matching buttons (same job id)
            const allBtns = document.querySelectorAll(`.apply-btn[data-job-id="${jobId}"]`);
            allBtns.forEach(b => {
                b.classList.add('btn-primary');
                b.classList.remove('btn-secondary');
                b.dataset.applied = '1';
                b.textContent = 'Applied';
                b.style.pointerEvents = 'none';
            });

            // Update localStorage for dashboard recent applications (client-side reflection)
            const applications = JSON.parse(localStorage.getItem('jobApplications') || '[]');
            const now = new Date().toISOString();
            const entry = {
                id: data.application_id || `${jobId}-${now}`,
                jobId: jobId,
                jobTitle: title,
                company: company,
                appliedDate: now,
                status: 'Under Review',
                matchScore: Math.floor(70 + Math.random()*25) // placeholder score
            };
            // avoid duplicates by jobId
            const exists = applications.some(a => parseInt(a.jobId) === jobId);
            if (!exists) applications.unshift(entry);
            localStorage.setItem('jobApplications', JSON.stringify(applications.slice(0,10)));

            if (window.Dashboard && typeof window.Dashboard.loadDashboardData === 'function') {
                // If dashboard is open in same page lifecycle, refresh widgets
                window.Dashboard.loadDashboardData();
            }

            showNotification(`Applied to ${title} at ${company}`, 'success');
        } catch (err) {
            console.error(err);
            btn.textContent = originalText;
            showNotification(err.message || 'Failed to apply', 'error');
        }
    });
}

function setupEventListeners() {
    // Sort by dropdown
    const sortSelect = document.getElementById('sortBy');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sortValue = this.value;
            sortJobs(sortValue);
        });
    }
    
    // Search form
    const searchForm = document.getElementById('jobSearchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            filterJobs();
        });
    }
    
    // Advanced filters toggle
    const toggleFilters = document.getElementById('toggleFilters');
    const advancedFilters = document.getElementById('advancedFilters');
    if (toggleFilters && advancedFilters) {
        toggleFilters.addEventListener('click', function() {
            const isVisible = advancedFilters.style.display !== 'none';
            advancedFilters.style.display = isVisible ? 'none' : 'block';
            this.textContent = isVisible ? 'Advanced Filters' : 'Hide Filters';
        });
    }
    
    // Clear filters
    const clearFilters = document.getElementById('clearFilters');
    if (clearFilters) {
        clearFilters.addEventListener('click', function() {
            clearAllFilters();
        });
    }
    
    // Salary toggle functionality
    setupSalaryToggle();
    
    // Sidebar filters
    setupSidebarFilters();
    
    // Real-time search as user types
    const searchInputs = document.querySelectorAll('#jobTitle, #location, #salaryRange');
    const debouncedFilter = debounceSearch(filterJobs, 300);
    
    searchInputs.forEach(input => {
        input.addEventListener('input', debouncedFilter);
    });
    
    // Add change listeners for dropdowns
    const dropdownInputs = document.querySelectorAll('#category, #experience, #jobType');
    dropdownInputs.forEach(input => {
        input.addEventListener('change', debouncedFilter);
    });

    // Salary dropdown menu click handlers
    const salaryMenu = document.getElementById('salaryMenu');
    const salaryToggleBtn = document.getElementById('salaryToggle');
    if (salaryToggleBtn && salaryMenu) {
        // Fallback toggle in case Bootstrap dropdown isn't active in this context
        salaryToggleBtn.addEventListener('click', (e) => {
            // Let Bootstrap handle it if present; otherwise, manually toggle
            // Manual toggle: add/remove 'show' and set positioning width to input group
            const parentDropdown = salaryToggleBtn.closest('.dropdown') || salaryToggleBtn.parentElement;
            const isShown = salaryMenu.classList.contains('show');
            if (isShown) {
                salaryMenu.classList.remove('show');
            } else {
                // Match menu width to container
                const group = parentDropdown.querySelector('.input-group');
                if (group) {
                    // Prefer content-fit width with a reasonable minimum
                    const width = Math.max(280, group.offsetWidth);
                    salaryMenu.style.minWidth = width + 'px';
                    salaryMenu.style.width = 'auto';
                }
                salaryMenu.classList.add('show');
                const rect = salaryToggleBtn.getBoundingClientRect();
                salaryMenu.style.left = '0px';
            }
            e.stopPropagation();
        });

        // Close when clicking outside
        document.addEventListener('click', (evt) => {
            if (!salaryMenu.classList.contains('show')) return;
            const target = evt.target;
            if (!salaryMenu.contains(target) && target !== salaryToggleBtn) {
                salaryMenu.classList.remove('show');
            }
        });

        salaryMenu.addEventListener('click', (e) => {
            const target = e.target;
            if (target.classList.contains('salary-option')) {
                e.preventDefault();
                const range = target.getAttribute('data-range');
                // Put minimum into input for display; full range handled in filtering
                const salaryInput = document.getElementById('salaryRange');
                if (salaryInput) {
                    if (range.includes('+')) {
                        salaryInput.value = parseInt(range);
                    } else {
                        salaryInput.value = parseInt(range.split('-')[0]);
                    }
                }
                filterJobs();
                // Close menu after selection
                salaryMenu.classList.remove('show');
            }
            if (target.id === 'salaryClear') {
                e.preventDefault();
                const salaryInput = document.getElementById('salaryRange');
                if (salaryInput) salaryInput.value = '';
                filterJobs();
                salaryMenu.classList.remove('show');
            }
        });
    }
}

function setupSalaryToggle() {
    const salaryToggle = document.getElementById('salaryToggle');
    const salaryInput = document.getElementById('salaryRange');
    const salaryDropdown = document.getElementById('salaryDropdown');
    
    if (salaryToggle && salaryInput && salaryDropdown) {
        salaryToggle.addEventListener('click', function() {
            const isDropdownVisible = salaryDropdown.style.display !== 'none';
            
            if (isDropdownVisible) {
                // Switch to input mode
                salaryInput.style.display = 'block';
                salaryDropdown.style.display = 'none';
                salaryToggle.innerHTML = '<i class="fas fa-chevron-down"></i>';
                salaryToggle.title = 'Switch to dropdown';
            } else {
                // Switch to dropdown mode
                salaryInput.style.display = 'none';
                salaryDropdown.style.display = 'block';
                salaryToggle.innerHTML = '<i class="fas fa-keyboard"></i>';
                salaryToggle.title = 'Switch to input';
            }
        });
        
        // Sync values between input and dropdown
        salaryInput.addEventListener('input', function() {
            if (this.value) {
                // Find matching dropdown option based on input value
                const options = salaryDropdown.querySelectorAll('option');
                let matched = false;
                
                options.forEach(option => {
                    if (option.value.includes('+')) {
                        const minValue = parseInt(option.value.replace('+', ''));
                        if (parseInt(this.value) >= minValue) {
                            salaryDropdown.value = option.value;
                            matched = true;
                        }
                    } else if (option.value.includes('-')) {
                        const [min, max] = option.value.split('-').map(v => parseInt(v));
                        if (parseInt(this.value) >= min && parseInt(this.value) <= max) {
                            salaryDropdown.value = option.value;
                            matched = true;
                        }
                    }
                });
                
                if (!matched) {
                    salaryDropdown.value = '';
                }
            } else {
                salaryDropdown.value = '';
            }
        });
        
        salaryDropdown.addEventListener('change', function() {
            if (this.value) {
                if (this.value.includes('+')) {
                    const minValue = this.value.replace('+', '');
                    salaryInput.value = minValue;
                } else if (this.value.includes('-')) {
                    const [min] = this.value.split('-');
                    salaryInput.value = min;
                }
            } else {
                salaryInput.value = '';
            }
        });
    }
}

function setupSidebarFilters() {
    // Get all sidebar filter checkboxes
    const filterCheckboxes = document.querySelectorAll('#filtersSidebar input[type="checkbox"]');
    filterCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            filterJobs();
        });
    });
}

function sortJobs(sortBy) {
    const jobListings = document.getElementById('jobListings');
    const jobCards = Array.from(jobListings.querySelectorAll('.job-card'));
    
    // Add loading indicator
    jobListings.style.opacity = '0.7';
    
    // Small delay to show the loading state
    setTimeout(() => {
        switch(sortBy) {
            case 'salary':
                sortBySalary(jobCards);
                break;
            case 'date':
                sortByDate(jobCards);
                break;
            case 'relevance':
            default:
                sortByRelevance(jobCards);
                break;
        }
        
        // Re-append sorted cards
        jobCards.forEach(card => {
            jobListings.appendChild(card);
        });
        
        // Remove loading indicator
        jobListings.style.opacity = '1';
        
        // Update job count
        updateJobCount();
        
        // Show sort confirmation
        showSortConfirmation(sortBy);
    }, 200);
}

function sortBySalary(jobCards) {
    jobCards.sort((a, b) => {
        const salaryA = extractSalary(a);
        const salaryB = extractSalary(b);
        
        // Sort in descending order (highest salary first)
        return salaryB - salaryA;
    });
}

function extractSalary(jobCard) {
    // First try to get the max salary from data attribute (most reliable)
    const maxSalary = jobCard.getAttribute('data-salary-max');
    if (maxSalary) {
        return parseInt(maxSalary);
    }
    
    // Fallback to parsing the salary range data attribute
    const dataSalary = jobCard.getAttribute('data-salary');
    if (dataSalary) {
        const rangeMatch = dataSalary.match(/(\d+)-(\d+)/);
        if (rangeMatch) {
            return parseInt(rangeMatch[2]); // Return upper bound
        }
        const singleMatch = dataSalary.match(/(\d+)/);
        if (singleMatch) {
            return parseInt(singleMatch[1]);
        }
    }
    
    // Last resort: parse the displayed salary text
    const salaryText = jobCard.querySelector('.text-success.fw-bold')?.textContent || '';
    const salaryMatch = salaryText.match(/\$?([\d,]+)/g);
    
    if (salaryMatch && salaryMatch.length > 0) {
        const salaries = salaryMatch.map(match => {
            return parseInt(match.replace(/[\$,]/g, ''));
        });
        return Math.max(...salaries);
    }
    
    return 0; // Default for jobs without salary info
}

function sortByDate(jobCards) {
    jobCards.sort((a, b) => {
        const dateA = extractDate(a);
        const dateB = extractDate(b);
        
        // Sort in descending order (newest first)
        return dateB - dateA;
    });
}

function extractDate(jobCard) {
    // First try to get the date from data attribute (most reliable)
    const dataDate = jobCard.getAttribute('data-date');
    if (dataDate) {
        return parseInt(dataDate);
    }
    
    // Fallback to parsing the displayed date text
    const dateText = jobCard.querySelector('.text-muted.small')?.textContent || '';
    
    // Extract days from text like "Posted 2 days ago"
    const dayMatch = dateText.match(/(\d+)\s+day/i);
    if (dayMatch) {
        return parseInt(dayMatch[1]);
    }
    
    // Extract weeks from text like "Posted 1 week ago"
    const weekMatch = dateText.match(/(\d+)\s+week/i);
    if (weekMatch) {
        return parseInt(weekMatch[1]) * 7;
    }
    
    return 999; // Default for very old posts
}

function sortByRelevance(jobCards) {
    // Sort by match percentage (highest first)
    jobCards.sort((a, b) => {
        const matchA = extractMatchPercentage(a);
        const matchB = extractMatchPercentage(b);
        
        return matchB - matchA;
    });
}

function extractMatchPercentage(jobCard) {
    const matchBadge = jobCard.querySelector('.badge.bg-success, .badge.bg-warning, .badge.bg-info');
    if (matchBadge) {
        const matchText = matchBadge.textContent;
        const matchMatch = matchText.match(/(\d+)%/);
        if (matchMatch) {
            return parseInt(matchMatch[1]);
        }
    }
    
    return 0;
}

function filterJobs() {
    const searchTerm = document.getElementById('jobTitle')?.value.toLowerCase() || '';
    const location = document.getElementById('location')?.value.toLowerCase() || '';
    const category = document.getElementById('category')?.value || '';
    const experience = document.getElementById('experience')?.value || '';
    const jobType = document.getElementById('jobType')?.value || '';
    
    // Get salary value from either input or dropdown
    const salaryInput = document.getElementById('salaryRange');
    let salaryValue = salaryInput ? salaryInput.value : '';
    
    const jobCards = document.querySelectorAll('.job-card');
    let visibleCount = 0;
    
    jobCards.forEach(card => {
        let isVisible = true;
        
        // Search term filter
        if (searchTerm && isVisible) {
            const title = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
            const company = card.querySelector('.text-muted')?.textContent.toLowerCase() || '';
            const description = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
            
            isVisible = title.includes(searchTerm) || 
                       company.includes(searchTerm) || 
                       description.includes(searchTerm);
        }
        
        // Location filter
        if (location && isVisible) {
            const cardLocation = card.querySelector('.text-muted')?.textContent.toLowerCase() || '';
            isVisible = cardLocation.includes(location);
        }
        
        // Category filter
        if (category && isVisible) {
            const cardCategory = card.getAttribute('data-category') || '';
            isVisible = cardCategory === category;
        }
        
        // Experience filter
        if (experience && isVisible) {
            const cardExperience = card.getAttribute('data-experience') || '';
            isVisible = cardExperience === experience;
        }
        
        // Salary filter - handle both input value and dropdown range
        if (salaryValue && isVisible) {
            // If salaryValue looks like a range (contains '-' or '+'), use overlap logic
            if (/[+-]/.test(salaryValue)) {
                isVisible = checkSalaryRange(card, salaryValue);
            } else {
                // Treat as exact salary match (show jobs where salary equals the input)
                const targetSalary = parseInt(salaryValue);
                if (!isNaN(targetSalary) && targetSalary > 0) {
                    const cardMinSalary = extractMinSalary(card);
                    const cardMaxSalary = extractSalary(card);
                    isVisible = (cardMinSalary === targetSalary) || (cardMaxSalary === targetSalary);
                }
            }
        }
        
        // Job type filter
        if (jobType && isVisible) {
            const cardType = card.getAttribute('data-type') || '';
            isVisible = cardType === jobType;
        }
        
        // Show/hide card
        card.style.display = isVisible ? 'block' : 'none';
        
        if (isVisible) {
            visibleCount++;
        }
    });
    
    // Update job count
    const jobCountElement = document.getElementById('jobCount');
    if (jobCountElement) {
        jobCountElement.textContent = visibleCount;
    }
}

function checkSalaryRange(jobCard, range) {
    const cardMaxSalary = extractSalary(jobCard);
    const cardMinSalary = extractMinSalary(jobCard);
    
    if (range.includes('+')) {
        // Handle ranges like "150000+" - show jobs where minimum salary is at least this amount
        const minValue = parseInt(range.replace('+', ''));
        return cardMinSalary >= minValue;
    } else if (range.includes('-')) {
        // Handle ranges like "60000-100000" - show jobs that overlap with this range
        const [min, max] = range.split('-').map(v => parseInt(v));
        // Job is visible if it overlaps with the range (job min <= range max AND job max >= range min)
        return cardMinSalary <= max && cardMaxSalary >= min;
    }
    
    return true;
}

function extractMinSalary(jobCard) {
    // First try to get the min salary from data attribute
    const dataSalary = jobCard.getAttribute('data-salary');
    if (dataSalary) {
        const rangeMatch = dataSalary.match(/(\d+)-(\d+)/);
        if (rangeMatch) {
            return parseInt(rangeMatch[1]); // Return lower bound
        }
        const singleMatch = dataSalary.match(/(\d+)/);
        if (singleMatch) {
            return parseInt(singleMatch[1]);
        }
    }
    
    // Fallback to parsing the displayed salary text
    const salaryText = jobCard.querySelector('.text-success.fw-bold')?.textContent || '';
    const salaryMatch = salaryText.match(/\$?([\d,]+)/g);
    
    if (salaryMatch && salaryMatch.length > 0) {
        const salaries = salaryMatch.map(match => {
            return parseInt(match.replace(/[\$,]/g, ''));
        });
        return Math.min(...salaries);
    }
    
    return 0;
}

function clearAllFilters() {
    // Clear form inputs
    const inputs = document.querySelectorAll('#jobSearchForm input, #jobSearchForm select');
    inputs.forEach(input => {
        if (input.type === 'text' || input.type === 'number' || input.type === 'select-one') {
            input.value = '';
        }
    });
    
    // Clear advanced filters
    const advancedInputs = document.querySelectorAll('#advancedFilters input, #advancedFilters select');
    advancedInputs.forEach(input => {
        if (input.type === 'text' || input.type === 'number' || input.type === 'select-one') {
            input.value = '';
        }
    });
    
    // Clear sidebar filters
    const sidebarInputs = document.querySelectorAll('#filtersSidebar input[type="checkbox"]');
    sidebarInputs.forEach(input => {
        input.checked = false;
    });
    
    // Reset salary fields specifically
    const salaryInput = document.getElementById('salaryRange');
    const salaryDropdown = document.getElementById('salaryDropdown');
    if (salaryInput) salaryInput.value = '';
    if (salaryDropdown) salaryDropdown.value = '';
    
    // Switch back to input mode
    if (salaryInput && salaryDropdown) {
        salaryInput.style.display = 'block';
        salaryDropdown.style.display = 'none';
        const salaryToggle = document.getElementById('salaryToggle');
        if (salaryToggle) {
            salaryToggle.innerHTML = '<i class="fas fa-chevron-down"></i>';
            salaryToggle.title = 'Switch to dropdown';
        }
    }
    
    // Reset job display
    const jobCards = document.querySelectorAll('.job-card');
    jobCards.forEach(card => {
        card.style.display = 'block';
    });
    
    // Reset sort to relevance
    const sortSelect = document.getElementById('sortBy');
    if (sortSelect) {
        sortSelect.value = 'relevance';
    }
    
    // Update job count
    updateJobCount();
}

function updateJobCount() {
    const visibleCards = document.querySelectorAll('.job-card[style*="block"], .job-card:not([style])');
    const jobCountElement = document.getElementById('jobCount');
    if (jobCountElement) {
        jobCountElement.textContent = visibleCards.length;
    }
}

function showSortConfirmation(sortBy) {
    const sortMessages = {
        'salary': 'Jobs sorted by salary (highest to lowest)',
        'date': 'Jobs sorted by date (newest first)',
        'relevance': 'Jobs sorted by relevance (best match first)'
    };
    
    // Create or update sort confirmation message
    let confirmationDiv = document.getElementById('sortConfirmation');
    if (!confirmationDiv) {
        confirmationDiv = document.createElement('div');
        confirmationDiv.id = 'sortConfirmation';
        confirmationDiv.className = 'alert alert-info alert-dismissible fade show mt-3';
        confirmationDiv.style.position = 'fixed';
        confirmationDiv.style.top = '100px';
        confirmationDiv.style.right = '20px';
        confirmationDiv.style.zIndex = '1050';
        confirmationDiv.style.minWidth = '300px';
        
        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'btn-close';
        closeButton.setAttribute('data-bs-dismiss', 'alert');
        closeButton.setAttribute('aria-label', 'Close');
        
        confirmationDiv.appendChild(closeButton);
        document.body.appendChild(confirmationDiv);
    }
    
    confirmationDiv.innerHTML = `
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <i class="fas fa-sort-amount-down me-2"></i>${sortMessages[sortBy]}
    `;
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        if (confirmationDiv) {
            confirmationDiv.style.opacity = '0';
            setTimeout(() => {
                if (confirmationDiv && confirmationDiv.parentNode) {
                    confirmationDiv.parentNode.removeChild(confirmationDiv);
                }
            }, 300);
        }
    }, 3000);
}

// Enhanced job search with debouncing
let searchTimeout;
function debounceSearch(func, delay) {
    return function(...args) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => func.apply(this, args), delay);
    };
}
