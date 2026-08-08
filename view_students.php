<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['hod', 'lecturer'])) {
    header('Location: login.php');
    exit();
}

$db = new SQLite3('attendance.db');

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
if ($course_id == 0) {
    die("Invalid course ID.");
}

$course = $db->querySingle("SELECT course_code, course_title, department FROM courses WHERE id = $course_id", true);
if (!$course) {
    die("Course not found.");
}

if ($_SESSION['role'] == 'lecturer') {
    $check = $db->querySingle("SELECT 1 FROM courses WHERE id = $course_id AND lecturer_id = {$_SESSION['user_id']}");
    if (!$check) {
        die("Unauthorized.");
    }
}

$students = [];
$stmt = $db->prepare("
    SELECT s.id, s.reg_no, s.name
    FROM students s
    JOIN enrollment e ON s.id = e.student_id
    WHERE e.course_id = :course_id
    ORDER BY s.name ASC
");
$stmt->bindValue(':course_id', $course_id, SQLITE3_INTEGER);
$result = $stmt->execute();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $students[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>View Students - <?php echo htmlspecialchars($course['course_code']); ?></title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn {
            display: inline-block;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            text-decoration: none;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8px;
            margin-top: 0;
        }
        .department-badge {
            background: #e9ecef;
            padding: 5px 12px;
            border-radius: 20px;
            display: inline-block;
            font-size: 14px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f1f3f5;
        }
        .no-data {
            text-align: center;
            color: #6c757d;
            padding: 30px;
            font-style: italic;
        }
        .back-link {
            margin-bottom: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="hod_dashboard.php" class="btn btn-secondary back-link">← Back to Dashboard</a>
    <h1><?php echo htmlspecialchars($course['course_code']); ?> - <?php echo htmlspecialchars($course['course_title']); ?></h1>
    <div class="department-badge">📁 Department: <?php echo htmlspecialchars($course['department']); ?></div>

    <h2>📋 Enrolled Students (<?php echo count($students); ?>)</h2>

    <?php if (count($students) == 0): ?>
        <div class="no-data">No students are enrolled in this course yet.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Reg No</th>
                    <th>Student Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['reg_no']); ?></td>
                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                    <td>
                        <a href="hod_view_student_detail.php?student_id=<?php echo $student['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn btn-primary">View Attendance</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>