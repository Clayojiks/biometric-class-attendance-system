//Handles password validation
document.addEventListener('DOMContentLoaded', function() {
    console.log('Profile page loaded');
    
    const passwordForm = document.getElementById('passwordForm');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePasswordMatch() {
        if (confirmPassword.value.length > 0) {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.style.borderColor = '#e74c3c';
                confirmPassword.style.backgroundColor = '#ffebee';
                return false;
            } else {
                confirmPassword.style.borderColor = '#27ae60';
                confirmPassword.style.backgroundColor = '#e8f5e9';
                return true;
            }
        } else {
            confirmPassword.style.borderColor = '#ddd';
            confirmPassword.style.backgroundColor = 'white';
            return true;
        }
    }
    
    if (newPassword && confirmPassword) {
        newPassword.addEventListener('input', validatePasswordMatch);
        confirmPassword.addEventListener('input', validatePasswordMatch);
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