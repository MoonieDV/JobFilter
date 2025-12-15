// Registration page UI functionality
document.addEventListener('DOMContentLoaded', function() {
    // Default selection
    selectRole('employee');
});

function selectRole(role) {
    // Normalize role value
    const normalizedRole = (role === 'employee' || role === 'job_seeker') ? 'job_seeker' : 'employer';
    
    // Update the hidden input value
    document.getElementById('role').value = normalizedRole;

    const employeeCard = document.getElementById('employeeCard');
    const employerCard = document.getElementById('employerCard');
    const employeeFields = document.getElementById('employeeFields');
    const employerFields = document.getElementById('employerFields');

    if (normalizedRole === 'job_seeker') {
        employeeCard.classList.add('selected');
        employerCard.classList.remove('selected');
        employeeFields.style.display = 'block';
        employerFields.style.display = 'none';

        clearEmployerValidation();
        
        // Add visual feedback
        employeeCard.style.transform = 'scale(1.05)';
        employerCard.style.transform = 'scale(1)';
    } else {
        employerCard.classList.add('selected');
        employeeCard.classList.remove('selected');
        employerFields.style.display = 'block';
        employeeFields.style.display = 'none';

        clearEmployeeValidation();
        
        // Add visual feedback
        employerCard.style.transform = 'scale(1.05)';
        employeeCard.style.transform = 'scale(1)';
    }
    
    // Store the selected role for form submission
    window.selectedRole = normalizedRole;
    console.log('Role selected:', normalizedRole);
    
    // Add smooth transitions
    employeeCard.style.transition = 'all 0.3s ease';
    employerCard.style.transition = 'all 0.3s ease';
}

// Clear validation styles for employer fields
function clearEmployerValidation() {
    const employerFields = ['companyName', 'companyRegNumber', 'companyAddress', 'companyPhone', 'companyLinkedIn'];
    employerFields.forEach(id => {
        const el = document.getElementById(id);
        el.classList.remove('is-invalid');
    });
}

// Clear validation styles for employee fields
function clearEmployeeValidation() {
    const employeeFields = ['jobTitle', 'employeePhone', 'dob', 'bio', 'resume'];
    employeeFields.forEach(id => {
        const el = document.getElementById(id);
        el.classList.remove('is-invalid');
    });
}

// Form validation on submit
document.getElementById('registrationForm').addEventListener('submit', function (e) {
    // Ensure the role is set from the selected role
    const selectedRole = window.selectedRole || document.getElementById('role').value;
    document.getElementById('role').value = selectedRole;
    
    const role = selectedRole;
    let isValid = true;
    
    console.log('Form submitting with role:', role);

    // Common fields
    const fullname = document.getElementById('fullname');
    const email = document.getElementById('email');
    const password = document.getElementById('password');

    if (!fullname.value.trim()) {
        fullname.classList.add('is-invalid');
        isValid = false;
    } else {
        fullname.classList.remove('is-invalid');
    }

    if (!validateEmail(email.value)) {
        email.classList.add('is-invalid');
        isValid = false;
    } else {
        email.classList.remove('is-invalid');
    }

    if (!password.value.trim()) {
        password.classList.add('is-invalid');
        isValid = false;
    } else {
        password.classList.remove('is-invalid');
    }

    if (role === 'employee' || role === 'job_seeker') {
        const employeePhone = document.getElementById('employeePhone');
        const dob = document.getElementById('dob');
        const resume = document.getElementById('resume');

        // Phone required & valid
        if (!validatePhone(employeePhone.value)) {
            employeePhone.classList.add('is-invalid');
            isValid = false;
        } else {
            employeePhone.classList.remove('is-invalid');
        }

        // DOB required and should be in the past
        if (!dob.value || new Date(dob.value) >= new Date()) {
            dob.classList.add('is-invalid');
            isValid = false;
        } else {
            dob.classList.remove('is-invalid');
        }

        // Resume is optional, but if provided, validate file type
        if (resume.files.length > 0) {
            const allowedExtensions = ['pdf', 'doc', 'docx'];
            const fileName = resume.files[0].name.toLowerCase();
            const extension = fileName.split('.').pop();
            if (!allowedExtensions.includes(extension)) {
                resume.classList.add('is-invalid');
                isValid = false;
            } else {
                resume.classList.remove('is-invalid');
            }
        } else {
            resume.classList.remove('is-invalid');
        }

        // jobTitle and bio are optional, no validation required

    } else if (role === 'employer') {
        const companyName = document.getElementById('companyName');
        const companyRegNumber = document.getElementById('companyRegNumber');
        const companyAddress = document.getElementById('companyAddress');
        const companyPhone = document.getElementById('companyPhone');
        const companyLinkedIn = document.getElementById('companyLinkedIn');

        if (!companyName.value.trim()) {
            companyName.classList.add('is-invalid');
            isValid = false;
        } else {
            companyName.classList.remove('is-invalid');
        }

        if (!companyRegNumber.value.trim()) {
            companyRegNumber.classList.add('is-invalid');
            isValid = false;
        } else {
            companyRegNumber.classList.remove('is-invalid');
        }

        if (!companyAddress.value.trim()) {
            companyAddress.classList.add('is-invalid');
            isValid = false;
        } else {
            companyAddress.classList.remove('is-invalid');
        }

        if (!validatePhone(companyPhone.value)) {
            companyPhone.classList.add('is-invalid');
            isValid = false;
        } else {
            companyPhone.classList.remove('is-invalid');
        }

        if (companyLinkedIn.value.trim() && !validateURL(companyLinkedIn.value)) {
            companyLinkedIn.classList.add('is-invalid');
            isValid = false;
        } else {
            companyLinkedIn.classList.remove('is-invalid');
        }
    }

    if (!isValid) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    this.classList.add('was-validated');
});

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email.toLowerCase());
}

function validatePhone(phone) {
    const re = /^\+?[0-9\s\-]{7,15}$/;
    return re.test(phone.trim());
}

function validateURL(url) {
    try {
        new URL(url);
        return true;
    } catch {
        return false;
    }
}
