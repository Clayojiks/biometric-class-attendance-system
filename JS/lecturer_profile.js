document.addEventListener('DOMContentLoaded', function() {
    console.log('Lecturer Profile page loaded');
    
    const newPassword = document.querySelector('input[name="new_password"]');
    const confirmPassword = document.querySelector('input[name="confirm_password"]');
    
    if (newPassword && confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.style.borderColor = '#e74c3c';
            } else {
                confirmPassword.style.borderColor = '#27ae60';
            }
        });
        
        newPassword.addEventListener('input', function() {
            if (confirmPassword.value.length > 0 && newPassword.value !== confirmPassword.value) {
                confirmPassword.style.borderColor = '#e74c3c';
            } else if (confirmPassword.value.length > 0) {
                confirmPassword.style.borderColor = '#27ae60';
            }
        });
    }
});