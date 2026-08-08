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

// Get student details
$stmt = $db->prepare("SELECT * FROM students WHERE id = :id");
$stmt->bindValue(':id', $student_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$student = $result->fetchArray(SQLITE3_ASSOC);

//Get enrolled courses with attendance
$stmt = $db->prepare("
    SELECT 
        c.id, 
        c.course_code, 
        c.course_title, 
        c.total_sessions,
        COUNT(CASE WHEN a.status = 'present' THEN 1 END) as attended,
        COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as missed
    FROM enrollment e
    JOIN courses c ON e.course_id = c.id
    LEFT JOIN attendance a ON a.course_id = c.id AND a.student_id = e.student_id
    WHERE e.student_id = :student_id
    GROUP BY c.id, c.course_code, c.course_title, c.total_sessions
");
$stmt->bindValue(':student_id', $student_id, SQLITE3_INTEGER);
$courses_result = $stmt->execute();

$courses = [];
$total_attended = 0;
$total_sessions_sum = 0;

while ($row = $courses_result->fetchArray(SQLITE3_ASSOC)) {
    // Ensure values are set (prevent NULL)
    $row['attended'] = $row['attended'] ?? 0;
    $row['missed'] = $row['missed'] ?? 0;
    
    // SAFETY: Cap attended at total_sessions (never exceed)
    if ($row['attended'] > $row['total_sessions']) {
        $row['attended'] = $row['total_sessions'];
    }
    
    // Calculate percentage (at 100%)
    $percentage = ($row['total_sessions'] > 0) ? round(($row['attended'] / $row['total_sessions']) * 100) : 0;
    if ($percentage > 100) $percentage = 100;
    $row['percentage'] = $percentage;
    
    // Determine eligibility (75% or higher)
    $row['eligible'] = ($row['percentage'] >= 75);
    
    $courses[] = $row;
    $total_attended += $row['attended'];
    $total_sessions_sum += $row['total_sessions'];
}

// Calculate overall statistics
$total_courses = count($courses);
$courses_eligible = 0;
foreach ($courses as $course) {
    if ($course['eligible']) {
        $courses_eligible++;
    }
}

$overall_percentage = ($total_sessions_sum > 0) ? round(($total_attended / $total_sessions_sum) * 100) : 0;
if ($overall_percentage > 100) $overall_percentage = 100;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Biometric Attendance System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
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
            <li class="active"><a href="student_dashboard.php">Dashboard</a></li>
            <li><a href="student_my_attendance.php">My Attendance</a></li>
            <li><a href="student_profile.php">Profile</a></li>
            <li><a href="disputes.php">Disputes</a></li>
        </ul>
    </div>
    
    <div class="container">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <h2>Hello, <?php echo htmlspecialchars($student_name); ?>!</h2>
            <p>Track your attendance for each unit. You need 75% in EACH unit to be eligible for examinations.</p>
            <div class="student-details">
                <span>📚 Reg No: <?php echo htmlspecialchars($reg_no); ?></span>
                <span>🎓 Program: <?php echo htmlspecialchars($student['program']); ?></span>
                <span>📖 Year: <?php echo $student['year']; ?> | Semester: 1</span>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_courses; ?></div>
                <div class="stat-label">Total Units</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $courses_eligible; ?></div>
                <div class="stat-label">Units Eligible (≥75%)</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_courses - $courses_eligible; ?></div>
                <div class="stat-label">Units At Risk (<75%)</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $overall_percentage; ?>%</div>
                <div class="stat-label">Overall Attendance</div>
            </div>
        </div>
        
        <!-- Units Table -->
        <div class="card">
            <div class="card-header">
                <h3>📋 My Units - Attendance by Unit</h3>
                <p class="eligibility-note">⚠️ 75% attendance required in EACH unit to sit for examinations</p>
            </div>
            <div class="card-body">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Unit Code</th>
                            <th>Unit Title</th>
                            <th>Attended</th>
                            <th>Missed</th>
                            <th>Total</th>
                            <th>Progress</th>
                            <th>%</th>
                            <th>Exam Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($course['course_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($course['course_title']); ?></td>
                            <td><?php echo $course['attended']; ?></td>
                            <td class="missed-count"><?php echo $course['missed']; ?></td>
                            <td><?php echo $course['total_sessions']; ?></td>
                            <td>
                                <div class="progress-bar">
                                    <?php 
                                    $fill_color = 'eligible';
                                    if (!$course['eligible']) {
                                        $fill_color = ($course['percentage'] >= 50) ? 'warning' : 'ineligible';
                                    }
                                    ?>
                                    <div class="progress-fill <?php echo $fill_color; ?>" style="width: <?php echo $course['percentage']; ?>%;"></div>
                                </div>
                            </span>
                            <td><strong><?php echo $course['percentage']; ?>%</strong></td>
                            <td>
                                <?php if ($course['eligible']): ?>
                                    <span class="eligible-badge">✅ ELIGIBLE</span>
                                <?php else: ?>
                                    <span class="ineligible-badge">❌ INELIGIBLE</span>
                                <?php endif; ?>
                            </span>
                        </span>
                        <?php endforeach; ?>
                    </tbody>
                </span>
            </div>
        </div>
        
        <!-- Info Box -->
        <div class="info-box">
            <strong>📌 Important Note:</strong> The 75% attendance requirement applies to <strong>EACH UNIT individually</strong>. 
            You must achieve 75% or higher in every unit to be eligible for all examinations.
            If you are ineligible for any unit, you cannot sit for that unit's exam.
        </div>
    </div>
    
    <div class="footer">
        <p><span class="system-status"></span> System Online | Biometric Attendance System v1.0 | MMU</p>
    </div>
    
    <script src="JS/student_dashboard.js"></script>
</body>
</html>

