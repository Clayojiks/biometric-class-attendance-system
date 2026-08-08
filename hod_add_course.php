<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hod') {
    header('Location: login.php');
    exit();
}

$db = new SQLite3('attendance.db');
$hod_department = $_SESSION['department'];
$message = '';
$error = '';

// Get ONLY lecturers from HOD's department
$lecturers = $db->query("SELECT id, name, staff_no FROM lecturers WHERE department = '{$hod_department}' ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_code = trim($_POST['course_code']);
    $course_title = trim($_POST['course_title']);
    $lecturer_id = intval($_POST['lecturer_id']);
    $total_sessions = intval($_POST['total_sessions']);
    
    $stmt = $db->prepare("INSERT INTO courses (course_code, course_title, lecturer_id, total_sessions, department) 
                          VALUES (:code, :title, :lid, :sessions, :dept)");
    $stmt->bindValue(':code', $course_code, SQLITE3_TEXT);
    $stmt->bindValue(':title', $course_title, SQLITE3_TEXT);
    $stmt->bindValue(':lid', $lecturer_id, SQLITE3_INTEGER);
    $stmt->bindValue(':sessions', $total_sessions, SQLITE3_INTEGER);
    $stmt->bindValue(':dept', $hod_department, SQLITE3_TEXT);
    
    if ($stmt->execute()) {
        $message = "✅ Course added successfully!";
    } else {
        $error = "❌ Failed to add course.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Course - HOD</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .form-container { max-width: 500px; margin: 0 auto; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .btn-save { background: #27ae60; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; text-align: center; }
        .success { background: #e8f5e9; color: #27ae60; }
        .error { background: #ffebee; color: #e74c3c; }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div><h1>BIOMETRIC ATTENDANCE SYSTEM</h1><p>HOD Panel - <?php echo htmlspecialchars($hod_department); ?></p></div>
        <div class="user-info">
            <span>Welcome, HOD <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="nav">
        <ul>
            <li><a href="hod_dashboard.php">Dashboard</a></li>
            <li class="active"><a href="hod_add_course.php">Add Course</a></li>
            <li><a href="hod_disputes.php">Disputes</a></li>
            <li><a href="hod_profile.php">Profile</a></li>
            <li><a href="hod_eligibility_courses.php">Generate Eligibility</a></li>
            
        </ul>
    </div>
    
    <div class="container">
        <div class="card">
            <div class="card-header"><h3>➕ Add New Course to <?php echo htmlspecialchars($hod_department); ?> Department</h3></div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="message success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="message error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="form-container">
                    <div class="form-group">
                        <label>Course Code</label>
                        <input type="text" name="course_code" placeholder="e.g., BIT 2401" required>
                    </div>
                    <div class="form-group">
                        <label>Course Title</label>
                        <input type="text" name="course_title" placeholder="e.g., Mobile App Development" required>
                    </div>
                    <div class="form-group">
                        <label>Assigned Lecturer</label>
                        <select name="lecturer_id" required>
                            <option value="">-- Select Lecturer --</option>
                            <?php while ($lec = $lecturers->fetchArray(SQLITE3_ASSOC)): ?>
                            <option value="<?php echo $lec['id']; ?>"><?php echo htmlspecialchars($lec['name']); ?> (<?php echo $lec['staff_no']; ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Total Sessions</label>
                        <input type="number" name="total_sessions" value="12" required>
                    </div>
                    <button type="submit" class="btn-save">Save Course</button>
                </form>
                
                <p style="margin-top: 20px;"><a href="hod_dashboard.php">← Back to Dashboard</a></p>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p><span class="system-status"></span> System Online | Biometric Attendance System v1.0 | MMU</p>
    </div>
</body>
</html>