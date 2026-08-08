// student_disputes.js - Handles dispute form validation and user interactions

document.addEventListener('DOMContentLoaded', function() {
    console.log('Student Disputes page loaded');
    
    const disputeForm = document.getElementById('disputeForm');
    const attendanceSelect = document.getElementById('attendance_id');
    const reasonTextarea = document.getElementById('reason');
    
    // Form validation
    if (disputeForm) {
        disputeForm.addEventListener('submit', function(e) {
            let isValid = true;
            let errorMessage = '';
            
            // Check if a session is selected
            if (!attendanceSelect.value) {
                isValid = false;
                errorMessage = 'Please select a session to dispute.';
                attendanceSelect.style.borderColor = '#e74c3c';
                attendanceSelect.focus();
            } else {
                attendanceSelect.style.borderColor = '#ddd';
            }
            
            // Check if reason is provided
            if (!reasonTextarea.value.trim()) {
                isValid = false;
                errorMessage = 'Please provide a reason for your dispute.';
                reasonTextarea.style.borderColor = '#e74c3c';
                if (attendanceSelect.value) reasonTextarea.focus();
            } else if (reasonTextarea.value.trim().length < 10) {
                isValid = false;
                errorMessage = 'Please provide a more detailed reason (at least 10 characters).';
                reasonTextarea.style.borderColor = '#e74c3c';
                reasonTextarea.focus();
            } else {
                reasonTextarea.style.borderColor = '#ddd';
            }
            
            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
            }
        });
        
        // Clear error styling on input
        attendanceSelect.addEventListener('change', function() {
            this.style.borderColor = '#ddd';
        });
        
        reasonTextarea.addEventListener('input', function() {
            if (this.value.trim().length >= 10) {
                this.style.borderColor = '#27ae60';
            } else if (this.value.trim().length > 0) {
                this.style.borderColor = '#e74c3c';
            } else {
                this.style.borderColor = '#ddd';
            }
        });
    }
    
    // Character counter for reason textarea
    if (reasonTextarea) {
        const charCounter = document.createElement('div');
        charCounter.className = 'char-counter';
        charCounter.style.fontSize = '0.65rem';
        charCounter.style.color = '#888';
        charCounter.style.textAlign = 'right';
        charCounter.style.marginTop = '5px';
        charCounter.innerHTML = '0 characters (minimum 10)';
        reasonTextarea.parentNode.appendChild(charCounter);
        
        reasonTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCounter.innerHTML = length + ' characters (minimum 10)';
            if (length >= 10) {
                charCounter.style.color = '#27ae60';
            } else {
                charCounter.style.color = '#888';
            }
        });
    }
    
    // Auto-expand textarea
    if (reasonTextarea) {
        function autoResize() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        }
        reasonTextarea.addEventListener('input', autoResize);
    }
    
    // Add confirmation before submitting
    if (disputeForm) {
        disputeForm.addEventListener('submit', function(e) {
            const selectedOption = attendanceSelect.options[attendanceSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const confirmMessage = 'Submit dispute for: ' + selectedOption.text + '\n\nReason: ' + reasonTextarea.value.trim() + '\n\nAre you sure you want to submit this dispute?';
                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                }
            }
        });
    }
    
    console.log('Student Disputes page ready');
});