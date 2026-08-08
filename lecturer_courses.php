<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'lecturer') {
    header('Location: login.php');
    exit();
}
//determine dashboard based on role
$dashboard_link = ($_SESSION['role'] == 'hod') ? 'hod_dashboard.php' : 'lecturer_dashboard.php';

$db = new SQLite3('attendance.db');
$lecturer_id = $_SESSION['user_id'];
$lecturer_name = $_SESSION['user_name'];

$stmt = $db->prepare("SELECT * FROM courses WHERE lecturer_id = :lecturer_id");
$stmt->bindValue(':lecturer_id', $lecturer_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$courses = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $courses[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Courses - Lecturer Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/lecturer_courses.css">
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
            <li class="active"><a href="lecturer_courses.php">My Courses</a></li>
           <!-- <li><a href="lecturer_reports.php">Attendance Reports</a></li> -->
            <li><a href="lecturer_eligibility.php">Eligibility List</a></li>
            <li><a href="lecturer_profile.php">Profile</a></li>
            <li><a href="lecturer_disputes.php">Disputes</a></li>
        </ul>
    </div>
    
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>📚 My Courses (<?php echo count($courses); ?>)</h3>
            </div>
            <div class="card-body">
                <div class="courses-list">
                    <?php foreach ($courses as $course): ?>
                    <div class="course-row">
                        <div class="course-info">
                            <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
                            <span class="course-title"><?php echo htmlspecialchars($course['course_title']); ?></span>
                            <span class="course-sessions">Sessions: <?php echo $course['total_sessions']; ?></span>
                        </div>
                        <div class="course-actions">
                            <button class="btn btn-outline view-students" data-course="<?php echo $course['id']; ?>">View Students</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p><span class="system-status"></span> System Online | Biometric Attendance System v1.0 | MMU</p>
    </div>
    
    <script src="JS/lecturer_courses.js"></script>
</body>
</html>