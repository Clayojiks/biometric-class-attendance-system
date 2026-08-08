document.addEventListener("DOMContentLoaded", function () {

    const downloadBtn = document.querySelector(".btn-download");

    if (downloadBtn) {
        downloadBtn.onclick = function (e) {
            e.preventDefault();

            const courseId = this.getAttribute("data-course-id");

            window.location.href =
                "hod_download_report.php?course_id=" + courseId;
        };
    }
});

    const eligibilityBtn = document.querySelector(".btn-eligibility");

    if (eligibilityBtn) {
        eligibilityBtn.onclick = function (e) {
            e.preventDefault();

            const courseId = this.getAttribute("data-course-id");

            window.location.href =
                "hod_generate_eligibility.php?course_id=" + courseId;
        };
    }
