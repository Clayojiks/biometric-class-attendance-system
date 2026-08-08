<?php
// enroll_fingerprint.php - Admin tool to enroll student fingerprint
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['hod', 'lecturer'])) {
    header('Location: login.php');
    exit();
}

$db = new SQLite3('attendance.db');
$message = '';
$error = '';

//fingerprint_id enrollment
$check_column = $db->query("PRAGMA table_info(students)");
$has_fingerprint_id = false;
while ($col = $check_column->fetchArray(SQLITE3_ASSOC)) {
    if ($col['name'] == 'fingerprint_id') {
        $has_fingerprint_id = true;
        break;
    }
}
if (!$has_fingerprint_id) {
    $db->exec("ALTER TABLE students ADD COLUMN fingerprint_id INTEGER DEFAULT NULL");
}

// Get all students without fingerprint_id
$students = [];
$result = $db->query("SELECT id, reg_no, name, program FROM students WHERE fingerprint_id IS NULL OR fingerprint_id = 0 ORDER BY name");
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $students[] = $row;
    }
}

// Handle fingerprint enrollment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = intval($_POST['student_id']);
    $fingerprint_id = intval($_POST['fingerprint_id']);
    
    if ($fingerprint_id < 1 || $fingerprint_id > 200) {
        $error = "Fingerprint ID must be between 1 and 200.";
    } else {
        // Check if fingerprint id already exists
        $check = $db->prepare("SELECT id FROM students WHERE fingerprint_id = :fid");
        $check->bindValue(':fid', $fingerprint_id, SQLITE3_INTEGER);
        $check_result = $check->execute();
        
        if ($check_result->fetchArray()) {
            $error = "Fingerprint ID {$fingerprint_id} is already assigned to another student!";
        } else {
            // Update student with fingerprint id
            $stmt = $db->prepare("UPDATE students SET fingerprint_id = :fid WHERE id = :sid");
            $stmt->bindValue(':fid', $fingerprint_id, SQLITE3_INTEGER);
            $stmt->bindValue(':sid', $student_id, SQLITE3_INTEGER);
            
            if ($stmt->execute()) {
                // AUTO-ENROLL student in all department courses
                // Get student's program to determine department
                $stmt2 = $db->prepare("SELECT program FROM students WHERE id = :sid");
                $stmt2->bindValue(':sid', $student_id, SQLITE3_INTEGER);
                $result2 = $stmt2->execute();
                $student_data = $result2->fetchArray(SQLITE3_ASSOC);
                
                // Determine department based on program two departments
                $department = '';
                $program_lower = strtolower($student_data['program']);
                if (strpos($program_lower, 'computer science') !== false) {
                    $department = 'Computer Science';
                } elseif (strpos($program_lower, 'information technology') !== false) {
                    $department = 'Information Technology';
                } elseif (strpos($program_lower, 'software') !== false) {
                    $department = 'Software Engineering';
                } else {
                    // Default: use the department from the first word
                    $department = 'Information Technology';
                }
                
                // Enroll student in all courses of that department
                $stmt3 = $db->prepare("
                    INSERT OR IGNORE INTO enrollment (student_id, course_id, semester, academic_year)
                    SELECT :sid, id, :semester, :academic_year
                    FROM courses
                    WHERE department = :dept
                ");
                $stmt3->bindValue(':sid', $student_id, SQLITE3_INTEGER);
                $stmt3->bindValue(':dept', $department, SQLITE3_TEXT);
                $stmt3->bindValue(':semester', 'Semester 1', SQLITE3_TEXT);
                $stmt3->bindValue(':academic_year', '2025/2026', SQLITE3_TEXT);
                $stmt3->execute();
                
                $message = "✅ Student enrolled successfully! Fingerprint ID {$fingerprint_id} assigned and student auto-enrolled in all {$department} courses.";
                
                // Refresh the student list (remove the enrolled student from the dropdown)
                $students = [];
                $result = $db->query("SELECT id, reg_no, name, program FROM students WHERE fingerprint_id IS NULL OR fingerprint_id = 0 ORDER BY name");
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $students[] = $row;
                }
            } else {
                $error = "Failed to update fingerprint ID.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fingerprint Enrollment</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container { max-width: 600px; margin: 0 auto; }
        .student-list { margin: 20px 0; }
        .student-item { padding: 10px; border-bottom: 1px solid #eee; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group select, .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
        .btn-submit { background: #27ae60; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-submit:hover { background: #219653; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .success { background: #e8f5e9; color: #27ae60; }
        .error { background: #ffebee; color: #e74c3c; }
        hr { margin: 20px 0; }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div><h1>BIOMETRIC ATTENDANCE SYSTEM</h1><p>Fingerprint Enrollment</p></div>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <div class="card-header"><h3>🖐️ Enroll Student Fingerprint</h3></div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="message success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if (count($students) > 0): ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Select Student</label>
                        <select name="student_id" required>
                            <option value="">-- Select Student --</option>
                            <?php foreach ($students as $student): ?>
                            <option value="<?php echo $student['id']; ?>">
                                <?php echo htmlspecialchars($student['reg_no']); ?> - <?php echo htmlspecialchars($student['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fingerprint ID (from Arduino)</label>
                        <input type="number" name="fingerprint_id" min="1" max="200" placeholder="Enter fingerprint ID (1-200)" required>
                        <small>The Arduino will show this ID after enrollment. Enter that number here.</small>
                    </div>
                    <button type="submit" class="btn-submit">Enroll Fingerprint & Auto-Enroll in Courses</button>
                </form>
                <?php else: ?>
                    <p>✅ All students have fingerprint IDs assigned!</p>
                <?php endif; ?>
                
                <hr>
                <h4>📋 Students still needing fingerprint enrollment:</h4>
                <div class="student-list">
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $student): ?>
                        <div class="student-item">
                            <?php echo htmlspecialchars($student['reg_no']); ?> - <?php echo htmlspecialchars($student['name']); ?>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No students left – all have fingerprints assigned.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>