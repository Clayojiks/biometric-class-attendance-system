function resolveDispute(disputeId, action) {
    const notes = document.getElementById('notes-' + disputeId).value;
    const actionText = action === 'approve' ? 'approve' : 'reject';
    
    if (confirm(`Are you sure you want to ${actionText} this dispute?`)) {
        fetch('lecturer_resolve_dispute.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                dispute_id: disputeId,
                action: action,
                resolution_notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Dispute resolved successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Network error: ' + error);
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Lecturer Disputes page loaded');
});