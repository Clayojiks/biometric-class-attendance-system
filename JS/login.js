// login.js
document.addEventListener('DOMContentLoaded', function() {
    const identifierInput = document.getElementById('identifier');
    const passwordInput = document.getElementById('password');
    const roleRadios = document.querySelectorAll('input[name="role"]');
    
    roleRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'student') {
                identifierInput.placeholder = 'e.g., MMU/CSC/2022/0456';
            } else {
                identifierInput.placeholder = 'e.g., MMU/STAFF/001';
            }
        });
    });
    
    document.querySelector('form').addEventListener('submit', function(e) {
        if (identifierInput.value.trim() === '') {
            e.preventDefault();
            alert('Please enter your Registration Number or Staff ID');
            identifierInput.focus();
        } else if (passwordInput.value.trim() === '') {
            e.preventDefault();
            alert('Please enter your password');
            passwordInput.focus();
        }
    });
});