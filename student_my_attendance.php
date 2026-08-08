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

// Get filter parameters
$filter_course = isset($_GET['course']) ? intval($_GET['course']) : 0;
$filter_week = isset($_GET['week']) ? intval($_GET['week']) : 0;

// Get all enrolled courses for filter dropdown
$stmt = $db->prepare("
    SELECT c.id, c.course_code, c.course_title 
    FROM enrollment e
    JOIN courses c ON e.course_id = c.id
    WHERE e.student_id = :student_id
");
$stmt->bindValue(':student_id', $student_id, SQLITE3_INTEGER);
$courses_result = $stmt->execute();
$courses = [];
while ($row = $courses_result->fetchArray(SQLITE3_ASSOC)) {
    $courses[] = $row;
}

// Build query for attendance records with filters
$query = "
    SELECT a.id, a.date, a.week, a.status, a.method,
           c.course_code, c.course_title
    FROM attendance a
    JOIN courses c ON a.course_id = c.id
    WHERE a.student_id = :student_id
";
if ($filter_course > 0) {
    $query .= " AND a.course_id = :course_id";
}
if ($filter_week > 0) {
    $query .= " AND a.week = :week";
}
$query .= " ORDER BY a.week ASC, c.course_code ASC";

$stmt = $db->prepare($query);
$stmt->bindValue(':student_id', $student_id, SQLITE3_INTEGER);
if ($filter_course > 0) {
    $stmt->bindValue(':course_id', $filter_course, SQLITE3_INTEGER);
}
if ($filter_week > 0) {
    $stmt->bindValue(':week', $filter_week, SQLITE3_INTEGER);
}

$attendance_result = $stmt->execute();
$attendance_records = [];
while ($row = $attendance_result->fetchArray(SQLITE3_ASSOC)) {
    $attendance_records[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance - Biometric Attendance System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/student_my_attendance.css">
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
            <li class="active"><a href="student_my_attendance.php">My Attendance</a></li>
            <li><a href="student_profile.php">Profile</a></li>
            <li><a href="disputes.php">Disputes</a></li>
        </ul>
    </div>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>📅 My Attendance Records by Unit and Week</h3>
            </div>
            <div class="card-body">
                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div class="filter-group">
                        <label>📚 Filter by Unit</label>
                        <select id="filterCourse">
                            <option value="0">-- All Units --</option>
                            <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>" <?php echo $filter_course == $course['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['course_code']); ?> - <?php echo htmlspecialchars($course['course_title']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>📊 Filter by Week</label>
                        <select id="filterWeek">
                            <option value="0">-- All Weeks --</option>
                            <?php for ($w = 1; $w <= 12; $w++): ?>
                            <option value="<?php echo $w; ?>" <?php echo $filter_week == $w ? 'selected' : ''; ?>>Week <?php echo $w; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button id="applyFilterBtn" class="filter-btn">🔍 Apply Filter</button>
                        <a href="student_my_attendance.php" class="filter-btn reset-btn">🔄 Reset</a>
                    </div>
                </div>
                
                <!-- Attendance Table -->
                <?php if (count($attendance_records) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Week</th>
                            <th>Unit Code</th>
                            <th>Unit Title</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance_records as $record): ?>
                        <tr>
                            <td><span class="week-badge">Week <?php echo $record['week']; ?></span></td>
                            <td><strong><?php echo htmlspecialchars($record['course_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($record['course_title']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                            <td>
                                <?php if ($record['status'] == 'present'): ?>
                                    <span class="present-badge">✓ Present</span>
                                <?php else: ?>
                                    <span class="absent-badge">✗ Absent</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo ucfirst($record['method']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="no-data">No attendance records found for the selected filters.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p><span class="system-status"></span> System Online | Biometric Attendance System v1.0 | MMU</p>
    </div>
    
    <script src="JS/student_my_attendance.js"></script>
</body>
</html>