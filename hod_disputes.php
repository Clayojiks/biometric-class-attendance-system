<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hod') {
    header('Location: login.php');
    exit();
}

$db = new SQLite3('attendance.db');
$hod_department = $_SESSION['department'];

// Get ALL disputes from HOD's department (view only - no resolve buttons)
$stmt = $db->prepare("
    SELECT d.id, d.reason, d.submitted_date, d.status, d.resolution_notes,
           s.name as student_name, s.reg_no,
           c.course_code, c.course_title,
           a.date, a.week,
           l.name as lecturer_name
    FROM disputes d
    JOIN attendance a ON d.attendance_id = a.id
    JOIN students s ON d.student_id = s.id
    JOIN courses c ON a.course_id = c.id
    JOIN lecturers l ON c.lecturer_id = l.id
    WHERE c.department = :dept
    ORDER BY d.submitted_date DESC
");
$stmt->bindValue(':dept', $hod_department, SQLITE3_TEXT);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOD - View Disputes</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/hod_disputes.css">
</head>
<body>
    <div class="dashboard-header">
        <div>
            <h1>BIOMETRIC ATTENDANCE SYSTEM</h1>
            <p>Multimedia University of Kenya | HOD Panel - <?php echo htmlspecialchars($hod_department); ?> Department</p>
        </div>
        <div class="user-info">
            <span>Welcome, HOD <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="nav">
        <ul>
            <li><a href="hod_dashboard.php">Dashboard</a></li>
            <li><a href="hod_add_course.php">Add Course</a></li>
            <li class="active"><a href="hod_disputes.php">
                View Disputes
                <?php if (count($pending_disputes) > 0): ?>
                    <span class="badge-count"><?php echo count($pending_disputes); ?></span>
                <?php endif; ?>
            </a></li>
            <li><a href="hod_profile.php">Profile</a></li>
            <li><a href="hod_eligibility_courses.php">Generate Eligibility</a></li>
            <!-- <li><a href="hod_reports.php">Reports</a></li> -->
        </ul>
    </div>
    
    <div class="container">
        <div class="info-box">
            <strong>ℹ️ Information:</strong> As HOD, you can <strong>view</strong> all disputes in your <?php echo htmlspecialchars($hod_department); ?> department. 
            Only the course lecturer can approve or reject disputes. This page shows the current status of all disputes.
        </div>
        
        <div class="disputes-container">
            <!-- Pending Disputes Column (View Only) -->
            <div class="card">
                <div class="card-header">
                    <h3>⏳ Pending Disputes (<?php echo count($pending_disputes); ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (count($pending_disputes) > 0): ?>
                        <?php foreach ($pending_disputes as $dispute): ?>
                        <div class="dispute-card">
                            <div class="dispute-header">
                                <div>
                                    <span class="student-name">👨‍🎓 <?php echo htmlspecialchars($dispute['student_name']); ?></span>
                                    <span class="student-reg">(<?php echo htmlspecialchars($dispute['reg_no']); ?>)</span>
                                </div>
                                <span class="pending-badge">Pending</span>
                            </div>
                            <div class="course-info">
                                📚 <strong><?php echo htmlspecialchars($dispute['course_code']); ?></strong> - <?php echo htmlspecialchars($dispute['course_title']); ?>
                            </div>
                            <div class="lecturer-name">
                                👨‍🏫 Course Lecturer: <?php echo htmlspecialchars($dispute['lecturer_name']); ?>
                            </div>
                            <div class="course-info">
                                📅 Date: <?php echo date('M d, Y', strtotime($dispute['date'])); ?> | Week <?php echo $dispute['week']; ?>
                            </div>
                            <div class="dispute-reason">
                                <strong>Student's Reason:</strong><br>
                                <?php echo nl2br(htmlspecialchars($dispute['reason'])); ?>
                            </div>
                            <div class="info-box" style="margin-top: 10px; padding: 8px; font-size: 0.7rem; background: #fff3e0; border-left-color: #e67e22;">
                                ⚠️ Only the course lecturer (<?php echo htmlspecialchars($dispute['lecturer_name']); ?>) can resolve this dispute.
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-data">✓ No pending disputes in your department.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Resolved Disputes Column (View Only) -->
            <div class="card">
                <div class="card-header">
                    <h3>✅ Resolved Disputes (<?php echo count($resolved_disputes); ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (count($resolved_disputes) > 0): ?>
                        <?php foreach ($resolved_disputes as $dispute): ?>
                        <div class="dispute-card">
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
                                📚 <strong><?php echo htmlspecialchars($dispute['course_code']); ?></strong> - <?php echo htmlspecialchars($dispute['course_title']); ?>
                            </div>
                            <div class="lecturer-name">
                                👨‍🏫 Course Lecturer: <?php echo htmlspecialchars($dispute['lecturer_name']); ?>
                            </div>
                            <div class="course-info">
                                📅 Date: <?php echo date('M d, Y', strtotime($dispute['date'])); ?> | Week <?php echo $dispute['week']; ?>
                            </div>
                            <div class="dispute-reason">
                                <strong>Student's Reason:</strong><br>
                                <?php echo nl2br(htmlspecialchars($dispute['reason'])); ?>
                            </div>
                            <?php if ($dispute['resolution_notes']): ?>
                            <div class="resolution-notes-display">
                                📝 Lecturer's Resolution: <?php echo htmlspecialchars($dispute['resolution_notes']); ?>
                            </div>
                            <?php endif; ?>
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
</body>
</html>