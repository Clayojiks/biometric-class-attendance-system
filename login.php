<?php
session_start();
$error = '';

$db = new SQLite3('attendance.db');

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = trim($_POST['identifier']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];
    
    // STUDENT LOGIN
    if ($role == 'student') {
        $stmt = $db->prepare("SELECT * FROM students WHERE reg_no = :identifier AND password = :password");
        $stmt->bindValue(':identifier', $identifier, SQLITE3_TEXT);
        $stmt->bindValue(':password', $password, SQLITE3_TEXT);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_type'] = 'student';
            $_SESSION['reg_no'] = $user['reg_no'];
            header('Location: student_dashboard.php');
            exit();
        } else {
            $error = 'Invalid Registration Number or Password';
        }
    }
    
    // LECTURER LOGIN (includes HOD)
    elseif ($role == 'lecturer') {
        $stmt = $db->prepare("SELECT * FROM lecturers WHERE staff_no = :identifier AND password = :password");
        $stmt->bindValue(':identifier', $identifier, SQLITE3_TEXT);
        $stmt->bindValue(':password', $password, SQLITE3_TEXT);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($user) {
            // Set common session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_type'] = 'lecturer';
            $_SESSION['staff_no'] = $user['staff_no'];
            
            // Set role and department for role-based access control
            $_SESSION['role'] = $user['role'] ?? 'lecturer';        // 'lecturer' or 'hod'
            $_SESSION['department'] = $user['department'] ?? '';    // 'IT' or 'SE'
            
            // Redirect based on role
            if ($user['role'] == 'hod') {
                header('Location: hod_dashboard.php');
            } else {
                header('Location: lecturer_dashboard.php');
            }
            exit();
        } else {
            $error = 'Invalid Staff ID or Password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Biometric Attendance System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-header">
            <div class="fingerprint-icon">🖐️</div>
            <h1>BIOMETRIC ATTENDANCE SYSTEM</h1>
            <p>Multimedia University of Kenya</p>
        </div>
        
        <div class="login-form">
            <h2>Welcome Back</h2>
            <p>Sign in to access your dashboard</p>
            
            <?php if($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <div class="input-group">
                    <label>Registration Number / Staff ID</label>
                    <input type="text" name="identifier" id="identifier" placeholder="e.g., MMU/CSC/2022/0456" required>
                </div>
                
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" id="password" placeholder="Enter your password" required>
                </div>
                
                <div class="role-group">
                    <label>Select Role</label>
                    <div class="role-options">
                        <label class="role-option">
                            <input type="radio" name="role" value="student" checked> 🎓 Student
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="lecturer"> 👨‍🏫 Lecturer
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">SIGN IN</button>
                
                <div class="login-footer">
                    <label class="checkbox">
                        <input type="checkbox"> Remember me
                    </label>
                    <a href="#">Forgot Password?</a>
                </div>
            </form>
            
            <div class="login-link">
                Don't have an account? <a href="register.php">Create Account</a>
            </div>
        </div>
        
        <div class="login-note">
            🔐 Students: After registration, enroll fingerprint at ICT desk
        </div>
    </div>
    
    <script src="JS/login.js"></script>
</body>
</html>