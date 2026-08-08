// lecturer_eligibility.js - Handles course selection and downloads

document.addEventListener('DOMContentLoaded', function() {
    console.log('Lecturer Eligibility page loaded');
    
    // Course selection buttons
    const courseButtons = document.querySelectorAll('.course-btn');
    const downloadPDFBtn = document.getElementById('downloadPDFBtn');
    const downloadCSVBtn = document.getElementById('downloadCSVBtn');
    
    // Handle course selection
    courseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const courseId = this.dataset.courseId;
            const page = document.body.dataset.role === 'hod'
    ? 'hod_eligibility.php'
    : 'lecturer_eligibility.php';

window.location.href = `${page}?course_id=${courseId}`;
        });
    });
    
    // Handle PDF download
    if (downloadPDFBtn) {
        downloadPDFBtn.addEventListener('click', function() {
            const courseId = this.dataset.courseId;
            if (courseId) {
                showLoading('Generating PDF...');
                window.location.href = `hod_download_report.php?course_id=${courseId}&format=pdf`;
                setTimeout(hideLoading, 1000);
            } else {
                alert('Please select a course first.');
            }
        });
    }
    
    // Handle CSV download
    if (downloadCSVBtn) {
        downloadCSVBtn.addEventListener('click', function() {
            const courseId = this.dataset.courseId;
            if (courseId) {
                showLoading('Generating CSV...');
                window.location.href = `hod_download_report.php?course_id=${courseId}&format=csv`;
                setTimeout(hideLoading, 1000);
            } else {
                alert('Please select a course first.');
            }
        });
    }
    
    // Add search functionality to tables
    addSearchToTable('eligibleTable', 'Search eligible students...');
    addSearchToTable('ineligibleTable', 'Search ineligible students...');
    
    // Add print functionality
    const printBtn = createPrintButton();
    if (printBtn && (document.getElementById('eligibleTable') || document.getElementById('ineligibleTable'))) {
        const downloadSection = document.querySelector('.download-section');
        if (downloadSection) {
            downloadSection.appendChild(printBtn);
        }
    }
});

// Add search box to table
function addSearchToTable(tableId, placeholder) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const searchContainer = document.createElement('div');
    searchContainer.className = 'table-search';
    searchContainer.style.margin = '10px 15px';
    searchContainer.style.display = 'flex';
    searchContainer.style.justifyContent = 'flex-end';
    
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = placeholder;
    searchInput.style.padding = '6px 12px';
    searchInput.style.border = '1px solid #ddd';
    searchInput.style.borderRadius = '20px';
    searchInput.style.fontSize = '0.75rem';
    searchInput.style.width = '200px';
    
    searchInput.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
    
    searchContainer.appendChild(searchInput);
    const tableCard = table.closest('.table-card');
    const tableHeader = tableCard.querySelector('.table-header');
    if (tableHeader) {
        tableHeader.insertAdjacentElement('afterend', searchContainer);
    }
}

// Create print button
function createPrintButton() {
    const printBtn = document.createElement('button');
    printBtn.className = 'btn-print';
    printBtn.innerHTML = '🖨️ Print List';
    printBtn.style.background = '#6c757d';
    printBtn.style.color = 'white';
    printBtn.style.border = 'none';
    printBtn.style.padding = '10px 24px';
    printBtn.style.borderRadius = '8px';
    printBtn.style.cursor = 'pointer';
    printBtn.style.fontWeight = '600';
    printBtn.style.fontSize = '0.85rem';
    printBtn.style.transition = 'all 0.3s ease';
    
    printBtn.addEventListener('mouseenter', function() {
        this.style.background = '#5a6268';
        this.style.transform = 'translateY(-2px)';
    });
    
    printBtn.addEventListener('mouseleave', function() {
        this.style.background = '#6c757d';
        this.style.transform = 'translateY(0)';
    });
    
    printBtn.addEventListener('click', function() {
        window.print();
    });
    
    return printBtn;
}

// Show loading indicator
function showLoading(message) {
    let loader = document.querySelector('.loading-overlay');
    if (!loader) {
        loader = document.createElement('div');
        loader.className = 'loading-overlay';
        loader.style.position = 'fixed';
        loader.style.top = '0';
        loader.style.left = '0';
        loader.style.width = '100%';
        loader.style.height = '100%';
        loader.style.background = 'rgba(0,0,0,0.7)';
        loader.style.display = 'flex';
        loader.style.justifyContent = 'center';
        loader.style.alignItems = 'center';
        loader.style.zIndex = '9999';
        loader.style.flexDirection = 'column';
        loader.style.gap = '15px';
        
        const spinner = document.createElement('div');
        spinner.style.width = '50px';
        spinner.style.height = '50px';
        spinner.style.border = '5px solid #f3f3f3';
        spinner.style.borderTop = '5px solid #8B0000';
        spinner.style.borderRadius = '50%';
        spinner.style.animation = 'spin 1s linear infinite';
        
        const msg = document.createElement('div');
        msg.textContent = message;
        msg.style.color = 'white';
        msg.style.fontSize = '1rem';
        
        loader.appendChild(spinner);
        loader.appendChild(msg);
        document.body.appendChild(loader);
        
        // Add keyframes for spinner
        if (!document.querySelector('#spinner-keyframes')) {
            const style = document.createElement('style');
            style.id = 'spinner-keyframes';
            style.textContent = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
            document.head.appendChild(style);
        }
    } else {
        loader.style.display = 'flex';
        const msgElement = loader.querySelector('div:last-child');
        if (msgElement) msgElement.textContent = message;
    }
}

// Hide loading indicator
function hideLoading() {
    const loader = document.querySelector('.loading-overlay');
    if (loader) {
        loader.style.display = 'none';
    }
}