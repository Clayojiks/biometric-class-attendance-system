// student_my_attendance.js - Handles filter functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('My Attendance page loaded');
    
    const applyFilterBtn = document.getElementById('applyFilterBtn');
    const filterCourse = document.getElementById('filterCourse');
    const filterWeek = document.getElementById('filterWeek');
    
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            const course = filterCourse.value;
            const week = filterWeek.value;
            window.location.href = 'student_my_attendance.php?course=' + course + '&week=' + week;
        });
    }
    
    // Add keyboard support (press Enter to apply filter)
    const filterInputs = [filterCourse, filterWeek];
    filterInputs.forEach(input => {
        if (input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const course = filterCourse.value;
                    const week = filterWeek.value;
                    window.location.href = 'student_my_attendance.php?course=' + course + '&week=' + week;
                }
            });
        }
    });
});