// hod_profile.js - Handles password validation
document.addEventListener('DOMContentLoaded', function() {
    console.log('HOD Profile page loaded');
    
    const passwordForm = document.getElementById('passwordForm');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    // Create password match indicator
    const matchIndicator = document.createElement('div');
    matchIndicator.className = 'password-match';
    confirmPassword.parentNode.appendChild(matchIndicator);
    
    function checkPasswordMatch() {
        if (confirmPassword.value.length > 0) {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.style.borderColor = '#e74c3c';
                confirmPassword.style.backgroundColor = '#ffebee';
                matchIndicator.innerHTML = '❌ Passwords do not match';
                matchIndicator.className = 'password-match invalid';
                return false;
            } else {
                confirmPassword.style.borderColor = '#27ae60';
                confirmPassword.style.backgroundColor = '#e8f5e9';
                matchIndicator.innerHTML = '✓ Passwords match';
                matchIndicator.className = 'password-match valid';
                return true;
            }
        } else {
            confirmPassword.style.borderColor = '#ddd';
            confirmPassword.style.backgroundColor = 'white';
            matchIndicator.innerHTML = '';
            return true;
        }
    }
    
    function checkPasswordStrength() {
        const pwd = newPassword.value;
        if (pwd.length > 0 && pwd.length < 4) {
            newPassword.style.borderColor = '#e74c3c';
        } else if (pwd.length >= 4) {
            newPassword.style.borderColor = '#27ae60';
        } else {
            newPassword.style.borderColor = '#ddd';
        }
    }
    
    if (newPassword && confirmPassword) {
        newPassword.addEventListener('input', function() {
            checkPasswordStrength();
            checkPasswordMatch();
        });
        confirmPassword.addEventListener('input', checkPasswordMatch);
    }
    
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            if (newPassword.value.length < 4) {
                e.preventDefault();
                alert('New password must be at least 4 characters long!');
                newPassword.focus();
            } else if (newPassword.value !== confirmPassword.value) {
                e.preventDefault();
                alert('New passwords do not match!');
                confirmPassword.focus();
            }
        });
    }
});