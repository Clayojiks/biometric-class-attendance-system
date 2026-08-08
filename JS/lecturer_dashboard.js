// lecturer_dashboard.js - Lecturer Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Lecturer Dashboard Loaded - Biometric Attendance System');
    
    // Add any interactive features here
    const startAttendanceBtns = document.querySelectorAll('.btn-primary');
    startAttendanceBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (this.textContent.includes('Start Attendance')) {
                console.log('Starting attendance session...');
            }
        });
    });
    
    // Simulate real-time updates
    setInterval(function() {
        // This would fetch new attendance data from the server
        console.log('Checking for new attendance records...');
    }, 30000); // Check every 30 seconds
});