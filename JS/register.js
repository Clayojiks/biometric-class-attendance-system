// register.js - Handles role switching and form validation

document.addEventListener('DOMContentLoaded', function() {
    console.log('Registration page loaded');
    
    // Get DOM elements
    const studentRadio = document.getElementById('roleStudent');
    const lecturerRadio = document.getElementById('roleLecturer');
    const studentFields = document.getElementById('studentFields');
    const lecturerFields = document.getElementById('lecturerFields');
    const studentNotice = document.getElementById('studentNotice');
    const form = document.getElementById('registerForm');
    const password = document.getElementById('password');
    const confirm = document.getElementById('confirm_password');
    
    // Function to toggle between Student and Lecturer fields
    function toggleFields() {
        if (studentRadio.checked) {
            // Show student fields, hide lecturer fields
            studentFields.style.display = 'block';
            lecturerFields.style.display = 'none';
            
            // Show student notice (fingerprint enrollment message)
            if (studentNotice) {
                studentNotice.style.display = 'block';
            }
            
            // Enable student inputs
            const studentInputs = studentFields.querySelectorAll('input, select');
            const lecturerInputs = lecturerFields.querySelectorAll('input, select');
            
            studentInputs.forEach(input => {
                input.disabled = false;
                input.required = true;
            });
            lecturerInputs.forEach(input => {
                input.disabled = true;
                input.required = false;
            });
        } else {
            // Show lecturer fields, hide student fields
            studentFields.style.display = 'none';
            lecturerFields.style.display = 'block';
            
            // Hide student notice (fingerprint enrollment message not needed for lecturers)
            if (studentNotice) {
                studentNotice.style.display = 'none';
            }
            
            // Enable lecturer inputs
            const studentInputs = studentFields.querySelectorAll('input, select');
            const lecturerInputs = lecturerFields.querySelectorAll('input, select');
            
            studentInputs.forEach(input => {
                input.disabled = true;
                input.required = false;
            });
            lecturerInputs.forEach(input => {
                input.disabled = false;
                input.required = true;
            });
        }
    }
    
    // Password match validation
    function checkPasswordMatch() {
        if (confirm.value.length > 0) {
            if (password.value !== confirm.value) {
                confirm.style.borderColor = '#e74c3c';
                confirm.style.backgroundColor = '#ffebee';
            } else {
                confirm.style.borderColor = '#27ae60';
                confirm.style.backgroundColor = '#e8f5e9';
            }
        } else {
            confirm.style.borderColor = '#ddd';
            confirm.style.backgroundColor = 'white';
        }
    }
    
    // Form validation before submit
    function validateForm(e) {
        let isValid = true;
        let errorMessage = '';
        
        // Check password match
        if (password.value !== confirm.value) {
            isValid = false;
            errorMessage = 'Passwords do not match!';
        }
        
        // Check password length
        if (password.value.length < 4) {
            isValid = false;
            errorMessage = 'Password must be at least 4 characters long.';
        }
        
        // Role-specific validation
        if (studentRadio.checked) {
            const regNo = document.getElementById('reg_no');
            const studentName = document.getElementById('student_name');
            const program = document.getElementById('program');
            const year = document.getElementById('year');
            
            if (!regNo.value.trim()) {
                isValid = false;
                errorMessage = 'Please enter Registration Number';
                regNo.focus();
            } else if (!studentName.value.trim()) {
                isValid = false;
                errorMessage = 'Please enter Full Name';
                studentName.focus();
            } else if (!program.value) {
                isValid = false;
                errorMessage = 'Please select Program of Study';
                program.focus();
            } else if (!year.value) {
                isValid = false;
                errorMessage = 'Please select Year of Study';
                year.focus();
            }
        } else if (lecturerRadio.checked) {
            const staffNo = document.getElementById('staff_no');
            const lecturerName = document.getElementById('lecturer_name');
            const department = document.getElementById('department');
            
            if (!staffNo.value.trim()) {
                isValid = false;
                errorMessage = 'Please enter Staff Number';
                staffNo.focus();
            } else if (!lecturerName.value.trim()) {
                isValid = false;
                errorMessage = 'Please enter Full Name';
                lecturerName.focus();
            } else if (!department.value) {
                isValid = false;
                errorMessage = 'Please select Department';
                department.focus();
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            alert(errorMessage);
        }
        
        return isValid;
    }
    
    // Add event listeners
    if (studentRadio && lecturerRadio) {
        studentRadio.addEventListener('change', toggleFields);
        lecturerRadio.addEventListener('change', toggleFields);
    }
    
    if (password && confirm) {
        password.addEventListener('input', checkPasswordMatch);
        confirm.addEventListener('input', checkPasswordMatch);
    }
    
    if (form) {
        form.addEventListener('submit', validateForm);
    }
    
    // Initial call to set correct state
    toggleFields();
    
    console.log('Registration page ready');
});