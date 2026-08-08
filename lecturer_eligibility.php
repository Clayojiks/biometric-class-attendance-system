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
$staff_no = $_SESSION['staff_no'];

// Get selected course from URL
$selected_course = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// HOD view all courses
if ($_SESSION['role'] == 'hod') {

    $department = $_SESSION['department'];

$stmt = $db->prepare("
    SELECT *
    FROM courses
    WHERE department = :department
    ORDER BY course_code
");

$stmt->bindValue(':department', $department, SQLITE3_TEXT);

$result = $stmt->execute();

} else {

    $stmt = $db->prepare("
        SELECT *
        FROM courses
        WHERE lecturer_id = :lecturer_id
        ORDER BY course_code
    ");

    $stmt->bindValue(':lecturer_id', $lecturer_id, SQLITE3_INTEGER);
}
$result = $stmt->execute();
$courses = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $courses[] = $row;
}

// Get students for selected course
$eligible_students = [];
$ineligible_students = [];
$current_course = null;
$total_students = 0;

if ($selected_course > 0) {
    // Get course details
    if ($_SESSION['role'] == 'hod') {

    $stmt = $db->prepare("
        SELECT *
        FROM courses
        WHERE id = :id
    ");

    $stmt->bindValue(':id', $selected_course, SQLITE3_INTEGER);

} else {

    $stmt = $db->prepare("
        SELECT *
        FROM courses
        WHERE id = :id
        AND lecturer_id = :lecturer_id
    ");

    $stmt->bindValue(':id', $selected_course, SQLITE3_INTEGER);
    $stmt->bindValue(':lecturer_id', $lecturer_id, SQLITE3_INTEGER);
}
    $stmt->bindValue(':id', $selected_course, SQLITE3_INTEGER);
    $stmt->bindValue(':lecturer_id', $lecturer_id, SQLITE3_INTEGER);
    $course_result = $stmt->execute();
    $current_course = $course_result->fetchArray(SQLITE3_ASSOC);
    
    if ($current_course) {
        // Get students with attendance
        $stmt = $db->prepare("
            SELECT 
                s.id, s.reg_no, s.name, s.program, s.year,
                COUNT(CASE WHEN a.status = 'present' THEN 1 END) as attended,
                :total_sessions as total,
                ROUND(COUNT(CASE WHEN a.status = 'present' THEN 1 END) * 100.0 / :total_sessions) as percentage
            FROM students s
            JOIN enrollment e ON s.id = e.student_id
            LEFT JOIN attendance a ON a.student_id = s.id AND a.course_id = :course_id
            WHERE e.course_id = :course_id
            GROUP BY s.id
            ORDER BY percentage DESC
        ");
        $stmt->bindValue(':course_id', $selected_course, SQLITE3_INTEGER);
        $stmt->bindValue(':total_sessions', $current_course['total_sessions'], SQLITE3_INTEGER);
        $students_result = $stmt->execute();
        
        while ($student = $students_result->fetchArray(SQLITE3_ASSOC)) {
            $student['attended'] = $student['attended'] ?? 0;
            $student['percentage'] = $student['percentage'] ?? 0;
            if ($student['percentage'] >= 75) {
                $eligible_students[] = $student;
            } else {
                $ineligible_students[] = $student;
            }
        }
        $total_students = count($eligible_students) + count($ineligible_students);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eligibility List - Lecturer Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/lecturer_eligibility.css">
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
           <!-- <li><a href="lecturer_reports.php">Attendance Reports</a></li>-->
            <li class="active"><a href="lecturer_eligibility.php">Eligibility List</a></li>
            <li><a href="lecturer_profile.php">Profile</a></li>
            <li><a href="lecturer_disputes.php">Disputes</a></li>
        </ul>
    </div>
    
    <div class="container">
        <div class="eligibility-container">
            <!-- Info Banner -->
            <div class="info-banner">
                <div class="info-icon">📋</div>
                <div class="info-content">
                    <h4>75% Attendance Rule</h4>
                    <p>Students with attendance of 75% or higher are <strong>ELIGIBLE</strong> to sit for final examinations. Students below 75% are <strong>INELIGIBLE</strong>.</p>
                </div>
            </div>
            
            <!-- Course Selector -->
            <div class="course-selector-card">
                <label class="selector-label">Select Course</label>
                <div class="course-buttons" id="courseButtons">
                    <?php foreach ($courses as $course): ?>
                    <button class="course-btn <?php echo $selected_course == $course['id'] ? 'active' : ''; ?>" data-course-id="<?php echo $course['id']; ?>" type="button">
                        <?php echo htmlspecialchars($course['course_code']); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php if ($current_course): ?>
                <!-- Stats Summary -->
                <div class="stats-summary">
                    <div class="stat-box">
                        <div class="stat-number"><?php echo $total_students; ?></div>
                        <div class="stat-label">Total Students</div>
                    </div>
                    <div class="stat-box eligible">
                        <div class="stat-number"><?php echo count($eligible_students); ?></div>
                        <div class="stat-label">Eligible (≥75%)</div>
                    </div>
                    <div class="stat-box ineligible">
                        <div class="stat-number"><?php echo count($ineligible_students); ?></div>
                        <div class="stat-label">Ineligible (<75%)</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number"><?php echo $total_students > 0 ? round((count($eligible_students) / $total_students) * 100) : 0; ?>%</div>
                        <div class="stat-label">Eligibility Rate</div>
                    </div>
                </div>
                
                <!-- Download Buttons -->
                <div class="download-section">
                  <button id="printPDFBtn" class="btn-pdf" type="button">
                         🖨️ Print / Save as PDF
</button>
                    <button id="downloadCSVBtn"class="btn-csv"type="button"
           data-course-id="<?php echo $selected_course; ?>">
    📊 Download as CSV (Excel)
</button>
                </div>
                
                <!-- Eligible Students Table -->
                <div class="table-card">
                    <div class="table-header eligible-header">
                        <span class="table-icon">✓</span>
                        <h3>Eligible Students (≥75%)</h3>
                        <span class="table-count"><?php echo count($eligible_students); ?> students</span>
                    </div>
                    <div class="table-body">
                        <?php if (count($eligible_students) > 0): ?>
                        <table class="data-table" id="eligibleTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Reg No</th>
                                    <th>Student Name</th>
                                    <th>Program</th>
                                    <th>Year</th>
                                    <th>Attended</th>
                                    <th>Total</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($eligible_students as $student): ?>
                                <tr class="eligible-row">
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($student['reg_no']); ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['program']); ?></td>
                                    <td>Year <?php echo $student['year']; ?></td>
                                    <td><?php echo $student['attended']; ?></td>
                                    <td><?php echo $student['total']; ?></td>
                                    <td class="percent-eligible"><?php echo $student['percentage']; ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p class="no-data">No eligible students for this course.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Ineligible Students -->
                <div class="table-card">
                    <div class="table-header ineligible-header">
                        <span class="table-icon">✗</span>
                        <h3>Ineligible Students (<75%) </h3>
                        <span class="table-count"><?php echo count($ineligible_students); ?> students</span>
                    </div>
                    <div class="table-body">
                        <?php if (count($ineligible_students) > 0): ?>
                        <table class="data-table" id="ineligibleTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Reg No</th>
                                    <th>Student Name</th>
                                    <th>Program</th>
                                    <th>Year</th>
                                    <th>Attended</th>
                                    <th>Total</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($ineligible_students as $student): ?>
                                <tr class="ineligible-row">
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($student['reg_no']); ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['program']); ?></td>
                                    <td>Year <?php echo $student['year']; ?></td>
                                    <td><?php echo $student['attended']; ?></td>
                                    <td><?php echo $student['total']; ?></td>
                                    <td class="percent-ineligible"><?php echo $student['percentage']; ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p class="no-data">No ineligible students for this course.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php elseif ($selected_course == 0): ?>
                <div class="empty-state">
                    <div class="empty-icon">📚</div>
                    <h3>Select a Course</h3>
                    <p>Please select a course from above to view the examination eligibility list.</p>
                </div>
            <?php else: ?>
                <div class="error-state">
                    <div class="error-icon">⚠️</div>
                    <h3>Course Not Found</h3>
                    <p>The selected course is not assigned to you or does not exist.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="footer">
        <p><span class="system-status"></span> System Online | Biometric Attendance System v1.0 | MMU</p>
    </div>
    
    <script src="JS/lecturer_eligibility.js"></script>
</body>
</html>