// lecturer_eligibility.js - Handles course selection and downloads
    
  document.addEventListener("DOMContentLoaded", function () {

    console.log("Lecturer Eligibility Loaded");

    // ==========================
    // COURSE SELECTION
    // ==========================
    const courseButtons = document.querySelectorAll(".course-btn");

    courseButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            const courseId = this.dataset.courseId;

            window.location.href =
                "lecturer_eligibility.php?course_id=" + courseId;

        });

    });

    // ==========================
    // PRINT / SAVE PDF
    // ==========================
    const printPDFBtn = document.getElementById("printPDFBtn");

    if (printPDFBtn) {

        printPDFBtn.addEventListener("click", function () {

            window.print();

        });

    }

    // ==========================
    // DOWNLOAD CSV
    // ==========================
    const downloadCSVBtn = document.getElementById("downloadCSVBtn");

    if (downloadCSVBtn) {

        downloadCSVBtn.addEventListener("click", function () {

            const courseId = this.dataset.courseId;

            if (!courseId) {

                alert("Please select a course first.");

                return;

            }

            window.location.href =
                "lecturer_download_eligibility.php?course_id=" +
                courseId +
                "&format=csv";

        });

    }

    // ==========================
    // SEARCH BOXES
    // ==========================
    addSearchToTable(
        "eligibleTable",
        "Search eligible students..."
    );

    addSearchToTable(
        "ineligibleTable",
        "Search ineligible students..."
    );

});

function addSearchToTable(tableId, placeholder) {

    const table = document.getElementById(tableId);

    if (!table) return;

    const searchContainer = document.createElement("div");

    searchContainer.className = "table-search";

    searchContainer.style.margin = "10px";

    searchContainer.style.textAlign = "right";

    const searchInput = document.createElement("input");

    searchInput.type = "text";

    searchInput.placeholder = placeholder;

    searchInput.style.padding = "8px";

    searchInput.style.width = "220px";

    searchInput.style.border = "1px solid #ccc";

    searchInput.style.borderRadius = "5px";

    searchContainer.appendChild(searchInput);

    const tableCard = table.closest(".table-card");

    if (tableCard) {

        const header = tableCard.querySelector(".table-header");
        if (header) {

            header.insertAdjacentElement(
                "afterend",
                searchContainer
            );
        }
    }
    searchInput.addEventListener("keyup", function () {

        const filter = this.value.toLowerCase();

        const rows = table.querySelectorAll("tbody tr");

        rows.forEach(function (row) {

            const text = row.textContent.toLowerCase();

            row.style.display =
                text.includes(filter) ? "" : "none";

        });

    });
}

window.addEventListener("error", function (e) {

    console.error("JavaScript Error:");

    console.error(e.message);

    console.error("Line:", e.lineno);

});
