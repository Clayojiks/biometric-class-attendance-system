<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'lecturer') {
    header('Location: login.php');
    exit();
}

// hod can also access this page but with limited functionality (no password change)
$is_hod = ($_SESSION['role'] == 'hod');

// Database connection
$db = new SQLite3('attendance.db');
$lecturer_id = $_SESSION['user_id'];
$lecturer_name = $_SESSION['user_name'];
$staff_no = $_SESSION['staff_no'];
$error = '';
$success = '';

$stmt = $db->prepare("SELECT * FROM lecturers WHERE id = :id");
$stmt->bindValue(':id', $lecturer_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$lecturer = $result->fetchArray(SQLITE3_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    if ($lecturer['password'] != $current) {
        $error = "Current password is incorrect!";
    } elseif (strlen($new) < 4) {
        $error = "New password must be at least 4 characters!";
    } elseif ($new != $confirm) {
        $error = "Passwords do not match!";
    } else {
        $stmt = $db->prepare("UPDATE lecturers SET password = :pwd WHERE id = :id");
        $stmt->bindValue(':pwd', $new, SQLITE3_TEXT);
        $stmt->bindValue(':id', $lecturer_id, SQLITE3_INTEGER);
        if ($stmt->execute()) {
            $success = "Password updated successfully!";
        } else {
            $error = "Update failed. Please try again.";
        }
    }
}
//determinig dashboard based on role
$dashboard_link = ($is_hod) ?  'hod_dashboard.php' : 'lecturer_dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Lecturer Dashboard</title>
    <link rel="stylesheet" href="css/lecturer_profile.css">
    <link rel="stylesheet" href="css/style.css">
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
            <!--<li><a href="lecturer_reports.php">Attendance Reports</a></li> -->
            <li><a href="lecturer_eligibility.php">Eligibility List</a></li>
            <li class="active"><a href="lecturer_profile.php">Profile</a></li>
            <li><a href="lecturer_disputes.php">Disputes</a></li>
        </ul>
    </div>
    
    <div class="container">
        <div class="profile-grid">
            <div class="card">
                <div class="card-header">
                    <h3>👤 Personal Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="info-label">Full Name:</div>
                        <div class="info-value"><?php echo htmlspecialchars($lecturer['name']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Staff Number:</div>
                        <div class="info-value"><?php echo htmlspecialchars($staff_no); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Department:</div>
                        <div class="info-value"><?php echo htmlspecialchars($lecturer['department']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Account Created:</div>
                        <div class="info-value"><?php echo date('M d, Y', strtotime($lecturer['created_at'])); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>🔒 Change Password</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="input-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required>
                        </div>
                        <div class="input-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" required>
                        </div>
                        <div class="input-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p><span class="system-status"></span> System Online | Biometric Attendance System v1.0 | MMU</p>
    </div>
    
    <script src="JS/lecturer_profile.js"></script>
</body>
</html>