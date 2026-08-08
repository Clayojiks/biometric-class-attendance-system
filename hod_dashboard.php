<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hod') {
    header('Location: login.php');
    exit();
}

$db = new SQLite3('attendance.db');
$hod_department = $_SESSION['department'];
$user_name = $_SESSION['user_name'];
$staff_no = $_SESSION['staff_no'];

// Get ONLY courses from HOD's department
$courses = [];
$stmt = $db->prepare("
    SELECT c.*, l.name as lecturer_name, COUNT(DISTINCT e.student_id) as student_count
    FROM courses c
    LEFT JOIN lecturers l ON c.lecturer_id = l.id
    LEFT JOIN enrollment e ON c.id = e.course_id
    WHERE c.department = :dept
    GROUP BY c.id, c.course_code, c.course_title, c.lecturer_id, c.total_sessions, c.department, c.semester, c.academic_year
    ORDER BY c.course_code ASC
");
$stmt->bindValue(':dept', $hod_department, SQLITE3_TEXT);
$result = $stmt->execute();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $courses[] = $row;
}

// Get all students
$department_students = [];
$students_stmt = $db->prepare("
    SELECT DISTINCT s.id, s.reg_no, s.name, s.program, s.year
    FROM students s
    JOIN enrollment e ON s.id = e.student_id
    JOIN courses c ON e.course_id = c.id
    WHERE c.department = :dept
    ORDER BY s.name ASC
");
$students_stmt->bindValue(':dept', $hod_department, SQLITE3_TEXT);
$students_result = $students_stmt->execute();

while ($student_row = $students_result->fetchArray(SQLITE3_ASSOC)) {
    // Get attendance summary for student
    $att_stmt = $db->prepare("
        SELECT 
            c.course_code,
            COUNT(CASE WHEN a.status = 'present' THEN 1 END) as attended,
            c.total_sessions,
            ROUND(COUNT(CASE WHEN a.status = 'present' THEN 1 END) * 100.0 / c.total_sessions) as percentage
        FROM courses c
        JOIN enrollment e ON c.id = e.course_id
        LEFT JOIN attendance a ON a.course_id = c.id AND a.student_id = :sid
        WHERE c.department = :dept AND e.student_id = :sid
        GROUP BY c.id
    ");
    $att_stmt->bindValue(':sid', $student_row['id'], SQLITE3_INTEGER);
    $att_stmt->bindValue(':dept', $hod_department, SQLITE3_TEXT);
    $att_result = $att_stmt->execute();
    
    $courses_attendance = [];
    $total_percentage = 0;
    $course_count = 0;
    while ($att = $att_result->fetchArray(SQLITE3_ASSOC)) {
        $att['attended'] = $att['attended'] ?? 0;
        $att['percentage'] = $att['percentage'] ?? 0;
        $courses_attendance[] = $att;
        $total_percentage += $att['percentage'];
        $course_count++;
    }
    $student_row['courses'] = $courses_attendance;
    $student_row['overall_percentage'] = $course_count > 0 ? round($total_percentage / $course_count) : 0;
    $department_students[] = $student_row;
}

// Get pending disputes
$pending_disputes = 0;
$stmt = $db->prepare("
    SELECT COUNT(*) as count FROM disputes d
    JOIN attendance a ON d.attendance_id = a.id
    JOIN courses c ON a.course_id = c.id
    WHERE c.department = :dept AND d.status = 'pending'
");
$stmt->bindValue(':dept', $hod_department, SQLITE3_TEXT);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);
    $pending_disputes = $row['count'] ?? 0;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>HOD Dashboard - <?php echo htmlspecialchars($hod_department); ?> Department</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/hod_dashboard.css">
    </head>
    <body>
        <div class="dashboard-header">
            <div><h1>BIOMETRIC ATTENDANCE SYSTEM</h1><p>Multimedia University of Kenya | Head Of Department</p></div>
            <div class="user-info">
                <span>Welcome, HOD <?php echo htmlspecialchars($user_name); ?> (<?php echo htmlspecialchars($hod_department); ?>)</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
        
        <div class="nav">
            <ul>
        <li class="active"><a href="hod_dashboard.php">Dashboard</a></li>
        <li><a href="hod_add_course.php">Add Course</a></li>
        <li><a href="hod_disputes.php">
            Disputes
            <?php if ($pending_disputes > 0): ?>
                <span class="badge"><?php echo $pending_disputes; ?></span>
            <?php endif; ?>
            
        </a></li>
        <li><a href="hod_profile.php">Profile</a></li>
        <li><a href="hod_eligibility_courses.php">Generate Eligibility</a></li>
        <!--<li><a href="hod_reports.php">Reports</a></li> -->
        <li><a href="enroll_fingerprint.php">Enroll Fingerprint</a></li>      
        
    </ul>
</div>

    <div class="container">
        <div class="welcome-banner">
            <h2><?php echo htmlspecialchars($hod_department); ?> Department</h2>
            <p>Manage courses and attendance for your department.</p>
            <div class="lecturer-details">
                <span>Staff No: <?php echo htmlspecialchars($staff_no); ?></span>
                <span class="hod-badge">👑 HOD</span>
            </div>
        </div>
    
    <!--Department Courses-->
    <div class="card">
        <div class="card-header">
            <h3>📚 Department Courses (<?php echo count($courses); ?>)</h3>
            <button class="btn-add" onclick="window.location.href='hod_add_course.php'">+ Add New Course</button>
        </div>
        <div class="card-body">
            <?php if (count($courses) > 0): ?>
                <div class="courses-grid">
                    <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <div class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></div>
                        <div class="course-title"><?php echo htmlspecialchars($course['course_title']); ?></div>
                        <div class="course-stats">

                            👨‍🎓 Students: <?php echo $course['student_count']; ?> | 📅 Sessions: <?php echo $course['total_sessions']; ?>
                            <br>👨‍🏫 Lecturer: <?php echo htmlspecialchars($course['lecturer_name'] ?? 'Unassigned'); ?>
                        </div>
                        <div class="action-buttons">

<a href="view_students.php?course_id=<?php echo $course['id']; ?>" class="btn-view">👨‍🎓 View Students</a>
<button class="btn-download" data-course-id="<?php echo $course['id']; ?>">📊 Download Report</button>
<button class="btn-eligibility" data-course-id="<?php echo $course['id']; ?>">📋 Eligibility List</button>
                    </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-data">No courses found in your department.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3>👨‍🎓 Student Attendance Overview</h3>
            <p>Track attendance progress for all students in the <?php echo htmlspecialchars($hod_department); ?> department</p>
        </div>
        <div class="card-body">
                <?php if (count($department_students) > 0): ?>
                    <div class="students-attendance-grid">
                        <?php foreach ($department_students as $student): ?>
                        <div class="student-attendance-card">
                            <div class="student-header">
                                <div class="student-name"><?php echo htmlspecialchars($student['name']); ?></div>
                                <div class="student-reg"><?php echo htmlspecialchars($student['reg_no']); ?></div>
                            </div>
                            <div class="student-info">
                                📚 <?php echo htmlspecialchars($student['program']); ?> | Year <?php echo $student['year']; ?>
                            </div>
                            <div class="attendance-summary">
                                <div class="overall-progress">
                                    <div class="progress-label">Overall Attendance</div>
                                    <div class="progress-bar-container">
                                        <div class="progress-fill <?php echo $student['overall_percentage'] >= 75 ? 'eligible' : ($student['overall_percentage'] >= 50 ? 'warning' : 'ineligible'); ?>" style="width: <?php echo $student['overall_percentage']; ?>%;"></div>
                                </div>
                                <div class="progress-percentage <?php echo $student['overall_percentage'] >= 75 ? 'eligible-text' : 'ineligible-text'; ?>">
                                    <?php echo $student['overall_percentage']; ?>%
                                </div>
                            </div>
                            <div class="courses-list">
                                <strong>📋 Course Attendance:</strong>
                                <?php foreach ($student['courses'] as $course_att): ?>
                                <div class="course-attendance-item">
                                    <span class="course-code"><?php echo htmlspecialchars($course_att['course_code']); ?></span>
                                    <div class="small-progress">
                                        <div class="small-progress-bar">
                                            <div class="small-progress-fill <?php echo $course_att['percentage'] >= 75 ? 'eligible' : ($course_att['percentage'] >= 50 ? 'warning' : 'ineligible'); ?>" style="width: <?php echo $course_att['percentage']; ?>%;"></div>
                                        </div>
                                        <span class="course-percentage"><?php echo $course_att['percentage']; ?>%</span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="student-actions">
                            <button class="btn-view-student" data-student-id="<?php echo $student['id']; ?>">📊 View Detailed Report</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                    <p class="no-data">No students found in your department.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p><span class="system-status"></span> System Online | Biometric Attendance System v1.0 | MMU</p>
    </div>
    
    <script src="JS/hod_dashboard.js"></script>
</body>
</html>