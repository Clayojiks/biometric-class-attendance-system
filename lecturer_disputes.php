<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'lecturer') {
    header('Location: login.php');
    exit();
}
$dashboard_link = ($_SESSION['role'] == 'hod') ? 'hod_dashboard.php' : 'lecturer_dashboard.php';

$db = new SQLite3('attendance.db');
$lecturer_id = $_SESSION['user_id'];
$lecturer_name = $_SESSION['user_name'];

$stmt = $db->prepare("
    SELECT d.id, d.reason, d.submitted_date, d.status,
           s.name as student_name, s.reg_no,
           c.course_code, c.course_title,
           a.date, a.week
    FROM disputes d
    JOIN attendance a ON d.attendance_id = a.id
    JOIN students s ON d.student_id = s.id
    JOIN courses c ON a.course_id = c.id
    WHERE c.lecturer_id = :lecturer_id
    ORDER BY CASE WHEN d.status = 'pending' THEN 0 ELSE 1 END, d.submitted_date DESC
");
$stmt->bindValue(':lecturer_id', $lecturer_id, SQLITE3_INTEGER);
$result = $stmt->execute();

$pending_disputes = [];
$resolved_disputes = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    if ($row['status'] == 'pending') {
        $pending_disputes[] = $row;
    } else {
        $resolved_disputes[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dispute Management - Lecturer Dashboard</title>
    <link rel="stylesheet" href="css/lecturer_disputes.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard-header">
        <div>
            <h1>BIOMETRIC ATTENDANCE SYSTEM</h1>
            <p>Multimedia University of Kenya</p>
        </div>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($lecturer_name); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="nav">
        <ul>
            <li><a href="<?php echo $dashboard_link; ?>">Dashboard</a></li>
            <li><a href="lecturer_courses.php">My Courses</a></li>
            <!-- <li><a href="lecturer_reports.php">Attendance Reports</a></li> -->
            <li><a href="lecturer_eligibility.php">Eligibility List</a></li>
            <li><a href="lecturer_profile.php">Profile</a></li>
            <li class="active">
    <a href="lecturer_disputes.php">
        Disputes
        <?php if (count($pending_disputes) > 0): ?>
            <span class="pending-count"><?php echo count($pending_disputes); ?></span>
        <?php endif; ?>
    </a>
</li>
        </ul>
    </div>
    
    <div class="container">
        <div class="disputes-container">
            <div class="card">
                <div class="card-header">
                    <h3>⏳ Pending Disputes (<?php echo count($pending_disputes); ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (count($pending_disputes) > 0): ?>
                        <?php foreach ($pending_disputes as $dispute): ?>
                        <div class="dispute-card" id="dispute-<?php echo $dispute['id']; ?>">
                            <div class="dispute-header">
                                <div>
                                    <span class="student-name">👨‍🎓 <?php echo htmlspecialchars($dispute['student_name']); ?></span>
                                    <span class="student-reg">(<?php echo htmlspecialchars($dispute['reg_no']); ?>)</span>
                                </div>
                                <span class="pending-badge">Pending</span>
                            </div>
                            <div class="course-info">
                                📚 <?php echo htmlspecialchars($dispute['course_code']); ?> - <?php echo htmlspecialchars($dispute['course_title']); ?>
                            </div>
                            <div class="course-info">
                                📅 Date: <?php echo date('M d, Y', strtotime($dispute['date'])); ?> | Week <?php echo $dispute['week']; ?>
                            </div>
                            <div class="dispute-reason">
                                <strong>Student's Reason:</strong><br>
                                <?php echo nl2br(htmlspecialchars($dispute['reason'])); ?>
                            </div>
                            <div class="resolution-section">
                                <textarea id="notes-<?php echo $dispute['id']; ?>" class="resolution-notes" placeholder="Add resolution notes (optional)" rows="2"></textarea>
                                <div class="resolution-buttons">
                                    <button class="btn-approve" onclick="resolveDispute(<?php echo $dispute['id']; ?>, 'approve')">✓ Approve - Mark as Present</button>
                                    <button class="btn-reject" onclick="resolveDispute(<?php echo $dispute['id']; ?>, 'reject')">✗ Reject - Keep as Absent</button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-data">✓ No pending disputes. All student disputes have been resolved.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>✅ Resolved Disputes (<?php echo count($resolved_disputes); ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (count($resolved_disputes) > 0): ?>
                        <?php foreach ($resolved_disputes as $dispute): ?>
                        <div class="dispute-card resolved">
                            <div class="dispute-header">
                                <div>
                                    <span class="student-name">👨‍🎓 <?php echo htmlspecialchars($dispute['student_name']); ?></span>
                                    <span class="student-reg">(<?php echo htmlspecialchars($dispute['reg_no']); ?>)</span>
                                </div>
                                <?php if ($dispute['status'] == 'resolved'): ?>
                                    <span class="resolved-badge">Approved</span>
                                <?php else: ?>
                                    <span class="rejected-badge">Rejected</span>
                                <?php endif; ?>
                            </div>
                            <div class="course-info">
                                📚 <?php echo htmlspecialchars($dispute['course_code']); ?> - <?php echo htmlspecialchars($dispute['course_title']); ?>
                            </div>
                            <div class="dispute-reason">
                                <strong>Student's Reason:</strong><br>
                                <?php echo nl2br(htmlspecialchars($dispute['reason'])); ?>
                            </div>
                            <div class="resolution-notes-display">
                                📝 Resolution: <?php echo htmlspecialchars($dispute['resolution_notes']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-data">No resolved disputes yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p><span class="system-status"></span> System Online | Biometric Attendance System v1.0 | MMU</p>
    </div>
    
    <script src="JS/lecturer_disputes.js"></script>
</body>
</html>