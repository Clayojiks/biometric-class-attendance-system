document.addEventListener('DOMContentLoaded', function () {

    const downloadBtn = document.querySelector('.btn-download');

    if (downloadBtn) {
        downloadBtn.addEventListener('click', function () {
            const courseId = this.dataset.courseId;
            window.location.href = 'hod_download_report.php?course_id=' + courseId;
        });
    }

    const eligibilityBtn = document.querySelector('.btn-eligibility');

    if (eligibilityBtn) {
        eligibilityBtn.addEventListener('click', function () {
            const courseId = this.dataset.courseId;
            window.location.href = 'hod_generate_eligibility.php?course_id=' + courseId;
        });
    }

});