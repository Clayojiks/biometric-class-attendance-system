// student_dashboard.js - Handles dashboard interactions
document.addEventListener('DOMContentLoaded', function() {
    console.log('Student Dashboard loaded');
    
    // Add hover effect to stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            console.log('Hovering over stat card');
        });
    });
    
    // Add confirmation before navigating to disputes
    const disputeLink = document.querySelector('a[href="student_disputes.php"]');
    if (disputeLink) {
        disputeLink.addEventListener('click', function(e) {
            console.log('Navigating to disputes page');
        });
    }
});