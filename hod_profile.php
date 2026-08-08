<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hod') {
    header('Location: login.php');
    exit();
}

$db = new SQLite3('attendance.db');
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$staff_no = $_SESSION['staff_no'];
$department = $_SESSION['department'] ?? '';

// Get user details
$stmt = $db->prepare("SELECT * FROM lecturers WHERE id = :id");
$stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);

$error = '';
$success = '';

// Handle password update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($user['password'] != $current_password) {
        $error = "Current password is incorrect!";
    } elseif (strlen($new_password) < 4) {
        $error = "New password must be at least 4 characters long!";
    } elseif ($new_password != $confirm_password) {
        $error = "New passwords do not match!";
    } else {
        $stmt = $db->prepare("UPDATE lecturers SET password = :pwd WHERE id = :id");
        $stmt->bindValue(':pwd', $new_password, SQLITE3_TEXT);
        $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
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
    <title>HOD Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/hod_profile.css">  
    <style>
        .profile-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        @media (max-width: 768px) { .profile-grid { grid-template-columns: 1fr; } }
        .info-row { display: flex; padding: 12px 0; border-bottom: 1px solid #eee; }
        .info-label { width: 140px; font-weight: 600; color: #555; }
        .info-value { flex: 1; color: #333; }
        .hod-badge { background: #f39c12; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; display: inline-block; margin-left: 10px; }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div><h1>BIOMETRIC ATTENDANCE SYSTEM</h1><p>HOD Panel - <?php echo htmlspecialchars($department); ?> Department</p></div>
        <div class="user-info">
            <span>Welcome, HOD <?php echo htmlspecialchars($user_name); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="nav">
        <ul>
            <li><a href="hod_dashboard.php">Dashboard</a></li>
            <li><a href="hod_add_course.php">Add Course</a></li>
            <li><a href="hod_disputes.php">Disputes</a></li>
            <li class="active"><a href="hod_profile.php">Profile</a></li>
            <li><a href="hod_eligibility_courses.php">Generate Eligibility</a></li>
            <!--<li><a href="hod_reports.php">Reports</a></li> -->
            
        </ul>
    </div>
    
    <div class="container">
        <div class="profile-grid">
            <div class="card">
                <div class="card-header">
                    <h3>👤 Personal Information <span class="hod-badge">HOD</span></h3>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="info-label">Full Name:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['name']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Staff Number:</div>
                        <div class="info-value"><?php echo htmlspecialchars($staff_no); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Department:</div>
                        <div class="info-value"><?php echo htmlspecialchars($department); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Role:</div>
                        <div class="info-value">Head of Department (HOD)</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Account Created:</div>
                        <div class="info-value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>🔒 Change Password</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div style="background:#ffebee; color:#c0392b; padding:10px; margin-bottom:20px; border-radius:5px;"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div style="background:#e8f5e9; color:#27ae60; padding:10px; margin-bottom:20px; border-radius:5px;"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div style="margin-bottom:15px;">
                            <label>Current Password</label>
                            <input type="password" name="current_password" style="width:100%; padding:8px;" required>
                        </div>
                        <div style="margin-bottom:15px;">
                            <label>New Password</label>
                            <input type="password" name="new_password" style="width:100%; padding:8px;" required>
                        </div>
                        <div style="margin-bottom:15px;">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" style="width:100%; padding:8px;" required>
                        </div>
                        <button type="submit" name="update_password" style="background:#003366; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer;">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p><span class="system-status"></span> System Online | Biometric Attendance System v1.0 | MMU</p>
    </div>
    <script src="JS/hod_profile.js"></script>
</body>
</html>