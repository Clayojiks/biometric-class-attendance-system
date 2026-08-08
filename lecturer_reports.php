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
    SELECT c.id, c.course_code, c.course_title, 
           COUNT(DISTINCT a.student_id) as total_students,
           AVG(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100 as avg_attendance
    FROM courses c
    LEFT JOIN enrollment e ON c.id = e.course_id
    LEFT JOIN attendance a ON c.id = a.course_id
    WHERE c.lecturer_id = :lecturer_id
    GROUP BY c.id
");
$stmt->bindValue(':lecturer_id', $lecturer_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$reports = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $row['avg_attendance'] = round($row['avg_attendance']);
    $reports[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Reports - Lecturer Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/lecturer_reports.css">
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
            <!
            <li><a href="<?php echo $dashboard_link; ?>">Dashboard</a></li>
            <li><a href="lecturer_courses.php">My Courses</a></li>
           <!-- <li class="active"><a href="lecturer_reports.php">Attendance Reports</a></li> -->
            <li><a href="lecturer_eligibility.php">Eligibility List</a></li>
            <li><a href="lecturer_profile.php">Profile</a></li>
            <li><a href="lecturer_disputes.php">Disputes</a></li>
        </ul>
    </div>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>📊 Attendance Summary Report</h3>
            </div>
            <div class="card-body">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Title</th>
                            <th>Total Students</th>
                            <th>Average Attendance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($report['course_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($report['course_title']); ?></td>
                            <td><?php echo $report['total_students']; ?></td>
                            <td>
                                <?php echo $report['avg_attendance']; ?>%
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $report['avg_attendance']; ?>%; background: <?php echo $report['avg_attendance'] >= 75 ? '#27ae60' : ($report['avg_attendance'] >= 60 ? '#f39c12' : '#e74c3c'); ?>"></div>
                                </div>
                            </td>
                            <td><button class="btn btn-outline download-report" data-course="<?php echo $report['id']; ?>">📄 Download Report</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p><span class="system-status"></span> System Online | Biometric Attendance System v1.0 | MMU</p>
    </div>
    
    <script src="JS/lecturer_reports.js"></script>
</body>
</html>