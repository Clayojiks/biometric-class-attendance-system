        <?php
        session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hod') {
        header('Location: login.php');
        exit();
        }

        $db = new SQLite3('attendance.db');
        $hod_department = $_SESSION['department'];
        $course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

        // Get course details
        $stmt = $db->prepare("SELECT c.*, l.name as lecturer_name FROM courses c LEFT JOIN lecturers l ON c.lecturer_id = l.id WHERE c.id = :cid AND c.department = :dept");
        $stmt->bindValue(':cid', $course_id, SQLITE3_INTEGER);
        $stmt->bindValue(':dept', $hod_department, SQLITE3_TEXT);
        $course = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if (!$course) {
        die("Course not found in your department.");
        }

        // Get students enrolled
        $stmt = $db->prepare("
        SELECT s.id, s.reg_no, s.name, s.program, s.year,
        COUNT(CASE WHEN a.status = 'present' THEN 1 END) as attended,
        :total_sessions as total,
        ROUND(COUNT(CASE WHEN a.status = 'present' THEN 1 END) * 100.0 / :total_sessions) as percentage
        FROM students s
        JOIN enrollment e ON s.id = e.student_id
        LEFT JOIN attendance a ON a.student_id = s.id AND a.course_id = :course_id
        WHERE e.course_id = :course_id
        GROUP BY s.id
        ORDER BY s.name
        ");
        $stmt->bindValue(':course_id', $course_id, SQLITE3_INTEGER);
        $stmt->bindValue(':total_sessions', $course['total_sessions'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        $students = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['attended'] = $row['attended'] ?? 0;
        $row['percentage'] = $row['percentage'] ?? 0;
        $students[] = $row;
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($course['course_code']); ?> - Students</title>
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/hod_course_students.css">
        </head>
        <body>
        <div class="dashboard-header">
        <div><h1>BIOMETRIC ATTENDANCE SYSTEM</h1><p>HOD Panel</p></div>
        <div class="user-info">
        <span>Welcome, HOD <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        </div>

        <div class="nav">
        <ul>
        <li><a href="hod_dashboard.php">Dashboard</a></li>
        <li class="active"><a href="#">Course Students</a></li>
        <li><a href="hod_disputes.php">Disputes</a></li>
        <li><a href="hod_profile.php">Profile</a></li>
        <li><a href="hod_eligibility_courses.php">Generate Eligibility</a></li>
        <!-- <li><a href="hod_reports.php">Reports</a></li> -->
        </ul>
        </div>

        <div class="container">
        <div class="card">
        <div class="card-header">
        <h3>👨‍🎓 Students Enrolled in <?php echo htmlspecialchars($course['course_code']); ?> - <?php echo htmlspecialchars($course['course_title']); ?></h3>
        <p>Lecturer: <?php echo htmlspecialchars($course['lecturer_name'] ?? 'Not Assigned'); ?></p>
        </div>
        <div class="card-body">
        <?php if (count($students) > 0): ?>
        <div class="summary-cards">
        <div class="summary-card">
        <div class="summary-number"><?php echo count($students); ?></div>
        <div>Total Students</div>
        </div>
        <div class="summary-card">
        <div class="summary-number eligible-number"><?php echo count(array_filter($students, fn($s) => $s['percentage'] >= 75)); ?></div>
        <div>Eligible (≥75%)</div>
        </div>
        <div class="summary-card">
        <div class="summary-number ineligible-number"><?php echo count(array_filter($students, fn($s) => $s['percentage'] < 75)); ?></div>
        <div>Ineligible (<75%)</div>
        </div>
        </div>

        <table class="data-table">
        <thead>
        <tr>
        <th>Reg No</th>
        <th>Student Name</th>
        <th>Program</th>
        <th>Year</th>
        <th>Attended</th>
        <th>Total</th>
        <th>%</th>
        <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $student): ?>
        <tr>
        <td><?php echo htmlspecialchars($student['reg_no']); ?></td>
        <td><?php echo htmlspecialchars($student['name']); ?></td>
        <td><?php echo htmlspecialchars($student['program']); ?></td>
        <td>Year <?php echo $student['year']; ?></td>
        <td><?php echo $student['attended']; ?> / <?php echo $student['total']; ?></td>
        <td class="<?php echo $student['percentage'] >= 75 ? 'eligible' : 'ineligible'; ?>"><?php echo $student['percentage']; ?>%</td>
        <td><?php echo $student['percentage'] >= 75 ? '✅ Eligible' : '❌ Ineligible'; ?></td>
        </tr>                <?php endforeach; ?>
        </tbody>
        </table>

        <div class="action-buttons">
        <button class="btn-download" data-course-id="<?php echo $course_id; ?>">📊 Download Report (CSV)</button>
        <button class="btn-eligibility" data-course-id="<?php echo $course_id; ?>">📋 Generate Eligibility List</button>
        <button class="btn-back" onclick="window.location.href='hod_dashboard.php'">← Back to Dashboard</button>
        </div>
        <?php else: ?>
        <p>No students enrolled in this course.</p>
        <?php endif; ?>
        </div>
        </div>
        </div>

        <div class="footer">
        <p><span class="system-status"></span> System Online | Biometric Attendance System v1.0 | MMU</p>
        </div>

        <script src="JS/hod_course_students.js"></script>
        </body>
        </html>