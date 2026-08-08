document.addEventListener('DOMContentLoaded', function() {
    const downloadButtons = document.querySelectorAll('.download-report');
    downloadButtons.forEach(button => {
        button.addEventListener('click', function() {
            const courseId = this.dataset.course;
            alert('Downloading report for course ID: ' + courseId);
        });
    });
});