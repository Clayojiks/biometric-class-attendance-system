<?php
session_start();
$error = '';
$success = '';

$db = new SQLite3('attendance.db');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'] ?? 'student';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } 
    elseif ($role == 'student') {
        $reg_no = trim($_POST['reg_no']);
        $name = trim($_POST['name']);
        $program = trim($_POST['program']);
        $year = intval($_POST['year']);
        
        $check = $db->prepare("SELECT id FROM students WHERE reg_no = :reg_no");
        $check->bindValue(':reg_no', $reg_no, SQLITE3_TEXT);
        $result = $check->execute();
        
        if ($result->fetchArray()) {
            $error = "Registration number already exists! <a href='login.php'>Login here</a>";
        } else {
            $insert = $db->prepare("INSERT INTO students (reg_no, name, program, year, password) 
                                    VALUES (:reg_no, :name, :program, :year, :password)");
            $insert->bindValue(':reg_no', $reg_no, SQLITE3_TEXT);
            $insert->bindValue(':name', $name, SQLITE3_TEXT);
            $insert->bindValue(':program', $program, SQLITE3_TEXT);
            $insert->bindValue(':year', $year, SQLITE3_INTEGER);
            $insert->bindValue(':password', $password, SQLITE3_TEXT);
            
            if ($insert->execute()) {
                $success = "Student account created successfully! <a href='login.php'>Login now</a>";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    } 
    elseif ($role == 'lecturer') {
        $staff_no = trim($_POST['staff_no']);
        $name = trim($_POST['name']);
        $department = trim($_POST['department']);
        
        $check = $db->prepare("SELECT id FROM lecturers WHERE staff_no = :staff_no");
        $check->bindValue(':staff_no', $staff_no, SQLITE3_TEXT);
        $result = $check->execute();
        
        if ($result->fetchArray()) {
            $error = "Staff number already exists! <a href='login.php'>Login here</a>";
        } else {
            $insert = $db->prepare("INSERT INTO lecturers (staff_no, name, department, password) 
                                    VALUES (:staff_no, :name, :department, :password)");
            $insert->bindValue(':staff_no', $staff_no, SQLITE3_TEXT);
            $insert->bindValue(':name', $name, SQLITE3_TEXT);
            $insert->bindValue(':department', $department, SQLITE3_TEXT);
            $insert->bindValue(':password', $password, SQLITE3_TEXT);
            
            if ($insert->execute()) {
                $success = "Lecturer account created successfully! <a href='login.php'>Login now</a>";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Biometric Attendance System</title>
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <div class="fingerprint-icon">📝</div>
            <h1>CREATE ACCOUNT</h1>
            <p>Biometric Attendance System | MMU</p>
        </div>
        
        <div class="register-form">
            <h2>Join the System</h2>
            <p>Create your account to access the attendance dashboard</p>
            
            <?php if($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm">
                <div class="role-selector">
                    <label>Select Account Type</label>
                    <div class="role-radio-group">
                        <label class="role-radio">
                            <input type="radio" name="role" value="student" id="roleStudent" checked> 🎓 Student
                        </label>
                        <label class="role-radio">
                            <input type="radio" name="role" value="lecturer" id="roleLecturer"> 👨‍🏫 Lecturer
                        </label>
                    </div>
                </div>
                
                <!-- Student Fields -->
                <div id="studentFields" class="student-fields">
                    <div class="notice-box" id="studentNotice">
                        ℹ️ After registration, visit the ICT desk to enroll your fingerprint for attendance marking.
                    </div>
                    <div class="input-group">
                        <label>📧 Registration Number <span class="required-star">*</span></label>
                        <input type="text" name="reg_no" id="reg_no" placeholder="e.g., MMU/CSC/2022/0456">
                    </div>
                    <div class="input-group">
                        <label>👤 Full Name <span class="required-star">*</span></label>
                        <input type="text" name="name" id="student_name" placeholder="e.g., John Ochieng">
                    </div>
                    <div class="input-group">
                        <label>🎓 Program of Study <span class="required-star">*</span></label>
                        <select name="program" id="program">
                            <option value="">-- Select Program --</option>
                            <option value="Computer Science">Bachelor of Science in Computer Science</option>
                            <option value="Information Technology">Bachelor of Science in Information Technology</option>
                            <option value="Software Engineering">Bachelor of Science in Software Engineering</option>
                            <option value="Business Information Technology">Bachelor of Business Information Technology</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>📖 Year of Study <span class="required-star">*</span></label>
                        <select name="year" id="year">
                            <option value="">-- Select Year --</option>
                            <option value="1">Year 1</option>
                            <option value="2">Year 2</option>
                            <option value="3">Year 3</option>
                            <option value="4">Year 4</option>
                        </select>
                    </div>
                </div>
                
                <!-- Lecturer Fields -->
                <div id="lecturerFields" class="lecturer-fields">
                    <div class="input-group">
                        <label>🆔 Staff Number <span class="required-star">*</span></label>
                        <input type="text" name="staff_no" id="staff_no" placeholder="e.g., MMU/STAFF/002">
                    </div>
                    <div class="input-group">
                        <label>👤 Full Name <span class="required-star">*</span></label>
                        <input type="text" name="name" id="lecturer_name" placeholder="e.g., Dr. Jane Kamau">
                    </div>
                    <div class="input-group">
                        <label>🏛️ Department <span class="required-star">*</span></label>
                        <select name="department" id="department">
                            <option value="">-- Select Department --</option>
                            <option value="Computer Science">Computer Science (CS)</option>
                            <option value="Information Technology">Information Technology (IT)</option>
                            <option value="Software Engineering">Software Engineering (SE)</option>
                            <option value="Computing Technology">Computing Technology (CT)</option>
                        </select>
                    </div>
                </div>
                
                <div class="input-group">
                    <label>🔒 Password <span class="required-star">*</span></label>
                    <input type="password" name="password" id="password" placeholder="Create a strong password">
                </div>
                
                <div class="input-group">
                    <label>🔒 Confirm Password <span class="required-star">*</span></label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password">
                </div>
                
                <button type="submit" class="register-btn">📝 CREATE ACCOUNT</button>
                
                <div class="login-link">
                    Already have an account? <a href="login.php">Sign in here</a>
                </div>
            </form>
        </div>
        
        <div class="register-note">
            🔐 After account creation, students must enroll their fingerprint at the ICT Help Desk
        </div>
    </div>
    
    <script src="JS/register.js"></script>
</body>
</html>