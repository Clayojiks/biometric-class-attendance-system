<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'lecturer') {
    header('Location: login.php');
    exit();
}

$db = new SQLite3('attendance.db');
$lecturer_id = $_SESSION['user_id'];
$lecturer_name = $_SESSION['user_name'];
$staff_no = $_SESSION['staff_no'];

// Get lecturer details
$stmt = $db->prepare("SELECT * FROM lecturers WHERE id = :id");
$stmt->bindValue(':id', $lecturer_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$lecturer = $result->fetchArray(SQLITE3_ASSOC);

// Get courses taught by this lecturer
$stmt = $db->prepare("SELECT * FROM courses WHERE lecturer_id = :lecturer_id");
$stmt->bindValue(':lecturer_id', $lecturer_id, SQLITE3_INTEGER);
$courses_result = $stmt->execute();
$courses = [];
while ($row = $courses_result->fetchArray(SQLITE3_ASSOC)) {
    $courses[] = $row;
}

// Get overall statistics
$total_students = 0;
$total_attendance_percentage = 0;
$at_risk_students = 0;

foreach ($courses as $course) {
    // Count students enrolled in this course
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM enrollment WHERE course_id = :course_id");
    $stmt->bindValue(':course_id', $course['id'], SQLITE3_INTEGER);
    $count_result = $stmt->execute();
    $count_row = $count_result->fetchArray(SQLITE3_ASSOC);
    $total_students += $count_row['count'];
    
    // Get average attendance for this course
    $stmt = $db->prepare("
        SELECT AVG(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100 as avg_attendance
        FROM attendance a
        JOIN enrollment e ON a.student_id = e.student_id AND a.course_id = e.course_id
        WHERE a.course_id = :course_id
    ");
    $stmt->bindValue(':course_id', $course['id'], SQLITE3_INTEGER);
    $avg_result = $stmt->execute();
    $avg_row = $avg_result->fetchArray(SQLITE3_ASSOC);
    $total_attendance_percentage += ($avg_row['avg_attendance'] ?? 0);
    
    // Count at-risk students (below 75%)
    $stmt = $db->prepare("
SELECT COUNT(*) AS total
FROM (

SELECT a.student_id FROM attendance a
WHERE a.course_id=:course_id
GROUP BY a.student_id

     HAVING
       COUNT(CASE
    WHEN status='present'
     THEN 1
           END
         )*100.0/COUNT(*) <75

     )
");
    $stmt->bindValue(':course_id',$course['id'],SQLITE3_INTEGER);
     $r=$stmt->execute()->fetchArray(SQLITE3_ASSOC);
       $at_risk_students += $r['total'];
}

$avg_attendance = (count($courses) > 0) ? round($total_attendance_percentage / count($courses)) : 0;

// Get at-risk students list for display
$at_risk_list = [];
$stmt = $db->prepare("
    SELECT s.reg_no, s.name, c.course_code, 
           COUNT(CASE WHEN a.status = 'present' THEN 1 END) as attended,
           COUNT(*) as total,
           ROUND(COUNT(CASE WHEN a.status = 'present' THEN 1 END) * 100.0 / COUNT(*)) as percentage
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    JOIN courses c ON a.course_id = c.id
    WHERE c.lecturer_id = :lecturer_id
    GROUP BY s.id, c.id
    HAVING percentage < 75
    LIMIT 10
");
$stmt->bindValue(':lecturer_id', $lecturer_id, SQLITE3_INTEGER);
$risk_result = $stmt->execute();
while ($row = $risk_result->fetchArray(SQLITE3_ASSOC)) {
    $at_risk_list[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lecturer Dashboard - Biometric Attendance System</title>
    <link rel="stylesheet" href="css/style.css"> 

</head>
<body>
    <div class="dashboard-header">
        <div>
            <h1>BIOMETRIC CLASS ATTENDANCE MANAGEMENT SYSTEM</h1>
            <p style="font-size:0.7rem;">Multimedia University of Kenya</p>
        </div>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($lecturer_name); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="nav">
        <ul>
            <li class="active"><a href="lecturer_dashboard.php">Dashboard</a></li>
            <li><a href="lecturer_courses.php">My Courses</a></li>
            <li><a href="lecturer_eligibility.php">Eligibility List</a></li>
            <li><a href="lecturer_profile.php">Profile</a></li>
            <li><a href="lecturer_disputes.php">Disputes</a></li>
                
                <!-- At risk students count -->
                
        </ul>
    </div>
    
    <div class="container">
        <!-- Welcome -->
        <div class="welcome-banner">
            <h2>Welcome, <?php echo htmlspecialchars($lecturer_name); ?>!</h2>
            <p>Manage attendance for your courses. Monitor student attendance.</p>
            <div class="student-details">
                <span>👨‍🏫 Staff No: <?php echo htmlspecialchars($staff_no); ?></span>
                <span>🏛️ Department: <?php echo htmlspecialchars($lecturer['department']); ?></span>
                <span>📅 Semester: 1 | Academic Year: 2025/2026</span>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($courses); ?></div>
                <div class="stat-label">My Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_students; ?></div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $avg_attendance; ?>%</div>
                <div class="stat-label">Average Attendance</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $at_risk_students; ?></div>
                <div class="stat-label">At-Risk Students</div>
            </div>
        </div>
        
        <!-- Today's Classes-->
        <div class="card">
            <div class="card-header">
                <h3>📅 Today's Classes</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <?php foreach ($courses as $course): ?>
                    <div style="flex: 1; min-width: 250px; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 10px; padding: 15px;">
                        <div style="font-weight: bold; color: #2c3e50;"><?php echo htmlspecialchars($course['course_code']); ?></div>
                        <div style="font-size:0.8rem; color:#666;"><?php echo htmlspecialchars($course['course_title']); ?></div>
                        <div style="font-size:0.75rem; color:#3498db; margin: 10px 0;">🕒 08:00 AM - 11:00 AM | Venue: EG 29 <?php echo $course['id']; ?></div>
                        <button class="btn btn-primary" style="width:100%;" onclick="alert('Starting attendance for <?php echo htmlspecialchars($course['course_code']); ?>. Ensure fingerprint sensor is connected.')">▶ Start Attendance</button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- My Courses Table -->
        <div class="card">
            <div class="card-header">
                <h3>📋 My Courses - Semester 1 (2025/2026)</h3>
            </div>
            <div class="card-body">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Title</th>
                            <th>Total Sessions</th>
                            <th>Sessions Completed</th>
                            <th>Average Attendance</th>
                    
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): 
                            // Get average attendance for this course
                            $stmt = $db->prepare("
                                SELECT AVG(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100 as avg_att
                                FROM attendance a
                                WHERE a.course_id = :course_id
                            ");
                            $stmt->bindValue(':course_id', $course['id'], SQLITE3_INTEGER);
                            $avg_result = $stmt->execute();
                            $avg_row = $avg_result->fetchArray(SQLITE3_ASSOC);
                            $avg_att = round($avg_row['avg_att'] ?? 0);
                            
                            // Get completed sessions
                            $stmt = $db->prepare("SELECT COUNT(DISTINCT week) as completed FROM attendance WHERE course_id = :course_id");
                            $stmt->bindValue(':course_id', $course['id'], SQLITE3_INTEGER);
                            $sess_result = $stmt->execute();
                            $sess_row = $sess_result->fetchArray(SQLITE3_ASSOC);
                            $completed = $sess_row['completed'] ?? 0;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($course['course_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($course['course_title']); ?></td>
                            <td><?php echo $course['total_sessions']; ?></td>
                            <td><?php echo $completed; ?></td>
                            <td>
                                <?php echo $avg_att; ?>%
                                <div class="progress-bar-container" style="margin-top:5px;">
                                    <div class="progress-fill <?php echo $avg_att >= 75 ? 'green' : ($avg_att >= 60 ? 'yellow' : 'red'); ?>" style="width: <?php echo $avg_att; ?>%"></div>
                                </div>
                            </td>
                            
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- At-Risk Students Section -->
        <div class="card">
            <div class="card-header">
                <h3>⚠️ At-Risk Students (Below 75% Attendance Threshold)</h3>
            </div>
            <div class="card-body">
                <?php if (count($at_risk_list) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Registration No.</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Attended / Total</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($at_risk_list as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['reg_no']); ?></td>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['course_code']); ?></td>
                            <td><?php echo $student['attended']; ?> / <?php echo $student['total']; ?></td>
                            <td class="missed-count"><?php echo $student['percentage']; ?>%</td>
                           
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p style="text-align:center; color:#27ae60;">✓ No at-risk students. All students are above 75% attendance threshold!</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="card">
            <div class="card-body">
                <div class="action-buttons">

    <button
        class="btn btn-primary"
        onclick="window.location.href='lecturer_download_eligibility.php?format=pdf'">
        📄 Download PDF
    </button>

    <button
        class="btn btn-outline"
        onclick="window.location.href='lecturer_download_eligibility.php?format=csv'">
        📊 Download CSV
    </button>

</div>
            </div>
        </div>
        
        <div class="footer">
            <p><span class="system-status"></span> System Online | Biometric Attendance System v1.0 | Multimedia University of Kenya</p>
            <p style="font-size:0.65rem; margin-top:5px;">Note: Attendance is captured via fingerprint sensors in classrooms.</p>
        </div>
    </div>
    <script src="JS/lecturer_dashboard.js"></script>
</body>
</html>