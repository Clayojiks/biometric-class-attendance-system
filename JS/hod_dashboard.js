// hod_dashboard.js
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-view').forEach(btn => {
        btn.addEventListener('click', function() {
            window.location.href = 'lecturer_course_students.php?course_id=' + this.dataset.courseId;
        });
    });
    
    document.querySelectorAll('.btn-download').forEach(btn => {
        btn.addEventListener('click', function() {
            window.location.href = 'hod_download_report.php?course_id=' + this.dataset.courseId + '&format=csv';
        });
    });
    
    document.querySelectorAll('.btn-eligibility').forEach(btn => {
        btn.addEventListener('click', function() {
            window.location.href = 'hod_generate_eligibility.php?course_id=' + this.dataset.courseId;
        });
    });
});