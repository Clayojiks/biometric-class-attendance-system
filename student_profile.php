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
$error = '';
$success = '';

// Get student details
$stmt = $db->prepare("SELECT * FROM students WHERE id = :id");
$stmt->bindValue(':id', $student_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$student = $result->fetchArray(SQLITE3_ASSOC);

// Get enrolled courses
$stmt = $db->prepare("
    SELECT c.course_code, c.course_title 
    FROM enrollment e
    JOIN courses c ON e.course_id = c.id
    WHERE e.student_id = :student_id
");
$stmt->bindValue(':student_id', $student_id, SQLITE3_INTEGER);
$courses_result = $stmt->execute();
$enrolled_courses = [];
while ($row = $courses_result->fetchArray(SQLITE3_ASSOC)) {
    $enrolled_courses[] = $row;
}

// Handle password update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($student['password'] != $current_password) {
        $error = "Current password is incorrect!";
    } elseif (strlen($new_password) < 4) {
        $error = "New password must be at least 4 characters long!";
    } elseif ($new_password != $confirm_password) {
        $error = "New passwords do not match!";
    } else {
        $stmt = $db->prepare("UPDATE students SET password = :pwd WHERE id = :id");
        $stmt->bindValue(':pwd', $new_password, SQLITE3_TEXT);
        $stmt->bindValue(':id', $student_id, SQLITE3_INTEGER);
        if ($stmt->execute()) {
            $success = "Password updated successfully!";
        } else {
            $error = "Failed to update password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Biometric Attendance System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/profile.css">
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
            <li><a href="student_my_attendance.php">My Attendance</a></li>
            <li class="active"><a href="student_profile.php">Profile</a></li>
            <li><a href="disputes.php">Disputes</a></li>
        </ul>
    </div>
    
    <div class="container">
        <div class="profile-grid">
            <!-- Personal Information Card -->
            <div class="card">
                <div class="card-header">
                    <h3>👤 Personal Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="info-label">Full Name:</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['name']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Registration Number:</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['reg_no']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Program of Study:</div>
                        <div class="info-value"><?php echo htmlspecialchars($student['program']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Year of Study:</div>
                        <div class="info-value">Year <?php echo $student['year']; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Fingerprint Status:</div>
                        <div class="info-value">
                            <?php if ($student['fingerprint_id']): ?>
                                <span class="fingerprint-enrolled">✓ Fingerprint Enrolled (ID: <?php echo $student['fingerprint_id']; ?>)</span>
                            <?php else: ?>
                                <span class="fingerprint-not-enrolled">⚠ Not Enrolled - Visit HOD's Office</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Account Created:</div>
                        <div class-><?php echo date('M d, Y', strtotime($student['created_at'])); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Enrolled Courses Card -->
            <div class="card">
                <div class="card-header">
                    <h3>📚 Enrolled Courses</h3>
                </div>
                <div class="card-body">
                    <?php if (count($enrolled_courses) > 0): ?>
                        <ul class="course-list">
                            <?php foreach ($enrolled_courses as $course): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($course['course_code']); ?></strong><br>
                                <span><?php echo htmlspecialchars($course['course_title']); ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="no-data">No courses enrolled.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Change Password Card -->
        <div class="card">
            <div class="card-header">
                <h3>🔒 Change Password</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div style="background: #ffebee; color: #c0392b; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #c0392b;">
                        <strong>❌ Error:</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div style="background: #e8f5e9; color: #2e7d32; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2e7d32;">
                        <strong>✅ Success:</strong> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="passwordForm">
                    <div class="input-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" id="current_password" required>
                    </div>
                    <div class="input-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" id="new_password" required>
                        <small class="password-hint">Password must be at least 4 characters</small>
                    </div>
                    <div class="input-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" required>
                    </div>
                    <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p><span class="system-status"></span> System Online | Biometric Attendance System v1.0 | MMU</p>
    </div>
    
    <script src="JS/student_profile.js"></script>
</body>
</html>