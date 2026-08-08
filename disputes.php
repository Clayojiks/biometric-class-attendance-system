<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header('Location: login.php');
    exit();
}

$db = new SQLite3('attendance.db');
$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['user_name'];
$reg_no = $_SESSION['reg_no'];
$success = '';
$error = '';

// Handle dispute submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_dispute'])) {
    $attendance_id = intval($_POST['attendance_id']);
    $reason = trim($_POST['reason']);
    
    if ($attendance_id && $reason) {
        // Check if dispute already exists
        $check = $db->prepare("SELECT id FROM disputes WHERE student_id = :sid AND attendance_id = :aid");
        $check->bindValue(':sid', $student_id, SQLITE3_INTEGER);
        $check->bindValue(':aid', $attendance_id, SQLITE3_INTEGER);
        $existing = $check->execute();
        
        if ($existing->fetchArray()) {
            $error = "You have already submitted a dispute for this session!";
        } else {
            $stmt = $db->prepare("INSERT INTO disputes (student_id, attendance_id, reason) VALUES (:sid, :aid, :reason)");
            $stmt->bindValue(':sid', $student_id, SQLITE3_INTEGER);
            $stmt->bindValue(':aid', $attendance_id, SQLITE3_INTEGER);
            $stmt->bindValue(':reason', $reason, SQLITE3_TEXT);
            
            if ($stmt->execute()) {
                $success = "Dispute submitted successfully! The lecturer will review your case.";
            } else {
                $error = "Failed to submit dispute. Please try again.";
            }
        }
    } else {
        $error = "Please select a session and provide a reason for your dispute.";
    }
}

// Get attendance records for dispute selection (only absent records)
$stmt = $db->prepare("
    SELECT a.id, a.date, a.week, a.time, c.course_code, c.course_title
    FROM attendance a
    JOIN courses c ON a.course_id = c.id
    WHERE a.student_id = :student_id AND a.status = 'absent'
    ORDER BY a.date DESC
");
$stmt->bindValue(':student_id', $student_id, SQLITE3_INTEGER);
$attendance_result = $stmt->execute();
$absent_records = [];
while ($row = $attendance_result->fetchArray(SQLITE3_ASSOC)) {
    $absent_records[] = $row;
}

// Get existing disputes
$stmt = $db->prepare("
    SELECT d.*, a.date, a.week, c.course_code, c.course_title
    FROM disputes d
    JOIN attendance a ON d.attendance_id = a.id
    JOIN courses c ON a.course_id = c.id
    WHERE d.student_id = :student_id
    ORDER BY d.submitted_date DESC
");
$stmt->bindValue(':student_id', $student_id, SQLITE3_INTEGER);
$disputes_result = $stmt->execute();
$existing_disputes = [];
while ($row = $disputes_result->fetchArray(SQLITE3_ASSOC)) {
    $existing_disputes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disputes - Biometric Attendance System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/disputes.css">
</head>
<body>
    <div class="dashboard-header">
        <div>
            <h1>BIOMETRIC ATTENDANCE SYSTEM</h1>
            <p>Multimedia University of Kenya</p>
        </div>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($student_name); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="nav">
        <ul>
            <li><a href="student_dashboard.php">Dashboard</a></li>
            <li><a href="student_my_attendance.php">My Attendance</a></li>
            <li><a href="student_profile.php">Profile</a></li>
            <li class="active"><a href="disputes.php">Disputes</a></li>
        </ul>
    </div>
    
    <div class="container">
        <div class="disputes-grid">
            <!-- Submit Dispute -->
            <div class="card">
                <div class="card-header">
                    <h3>⚠️ Submit Attendance Dispute</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <p class="dispute-info">
                        Select an absent record that you believe was recorded in error.
                    </p>
                    
                    <?php if (count($absent_records) > 0): ?>
                    <form method="POST" action="">
                        <div class="input-group">
                            <label>Select Session</label>
                            <select name="attendance_id" required>
                                <option value="">-- Select a session --</option>
                                <?php foreach ($absent_records as $record): ?>
                                <option value="<?php echo $record['id']; ?>">
                                    <?php echo date('M d, Y', strtotime($record['date'])); ?> - 
                                    <?php echo htmlspecialchars($record['course_code']); ?> - 
                                    <?php echo htmlspecialchars($record['course_title']); ?>
                                    (Week <?php echo $record['week']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="input-group">
                            <label>Reason for Dispute</label>
                            <textarea name="reason" rows="4" placeholder="Explain why you believe this attendance record is incorrect..." required></textarea>
                        </div>
                        
                        <button type="submit" name="submit_dispute" class="btn-submit">Submit Dispute</button>
                    </form>
                    <?php else: ?>
                        <div class="no-absent-records">
                            <p>✓ No absent records found!</p>
                            <p class="small-text">You have no absent records to dispute.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- My Disputes Card -->
            <div class="card">
                <div class="card-header">
                    <h3>📋 My Disputes</h3>
                </div>
                <div class="card-body">
                    <?php if (count($existing_disputes) > 0): ?>
                        <?php foreach ($existing_disputes as $dispute): ?>
                        <div class="dispute-item">
                            <div class="dispute-header">
                                <span class="dispute-course"><?php echo htmlspecialchars($dispute['course_code']); ?></span>
                                <span class="dispute-date"><?php echo date('M d, Y', strtotime($dispute['date'])); ?></span>
                            </div>
                            <div class="dispute-reason"><?php echo htmlspecialchars($dispute['reason']); ?></div>
                            <div class="dispute-status">
                                Status: 
                                <?php if ($dispute['status'] == 'pending'): ?>
                                    <span class="status-pending">⏳ Pending Review</span>
                                <?php elseif ($dispute['status'] == 'resolved'): ?>
                                    <span class="status-resolved">✅ Approved - Attendance Changed to Present</span>
                                <?php else: ?>
                                    <span class="status-rejected">❌ Rejected - Attendance remains Absent</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($dispute['resolution_notes']): ?>
                            <div class="dispute-resolution">
                                📝 Lecturer's Response: <?php echo htmlspecialchars($dispute['resolution_notes']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-data">No disputes submitted yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Info Card -->
        <div class="info-card">
            <div class="info-card-header">
                <span>ℹ️</span>
                <h4>How to Dispute Attendance</h4>
            </div>
            <div class="info-card-body">
                <ul class="info-list">
                    <li>Select the absent session from the dropdown menu</li>
                    <li>Provide a detailed reason for your dispute</li>
                    <li>Submit the form for lecturer review</li>
                    <li>Disputes are typically reviewed within 3-5 business days</li>
                    <li>The lecturer may contact you for additional verification</li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p><span class="system-status"></span> System Online | Biometric Attendance System v1.0 | MMU</p>
    </div>
    
    <script src="JS/disputes.js"></script>
</body>
</html>