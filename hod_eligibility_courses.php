<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hod') {
    header("Location: login.php");
    exit();
}

$db = new SQLite3("attendance.db");

$department = $_SESSION['department'];

// Get all courses in the HOD's department
$stmt = $db->prepare("
    SELECT c.id,
           c.course_code,
           c.course_title,
           c.total_sessions,
           l.name AS lecturer_name
    FROM courses c
    LEFT JOIN lecturers l
        ON c.lecturer_id = l.id
    WHERE c.department = :dept
    ORDER BY c.course_code
");

$stmt->bindValue(":dept", $department, SQLITE3_TEXT);

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

<title>Generate Eligibility</title>

<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/hod_eligibility_courses.css">
</head>

<body>

<div class="dashboard-header">

    <div>
        <h1>BIOMETRIC ATTENDANCE SYSTEM</h1>
        <p>HOD - Generate Eligibility</p>
    </div>

    <div class="user-info">
        Welcome,
        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
        |
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

</div>

<div class="nav">
<ul>

<li><a href="hod_dashboard.php">Dashboard</a></li>
<li><a href="hod_add_course.php">Add Course</a></li>
<li><a href="hod_disputes.php">Disputes</a></li>
<li><a href="hod_profile.php">Profile</a></li>
<li class="active"><a href="hod_eligibility_courses.php">Generate Eligibility</a></li>
<!-- <li><a href="hod_reports.php">Reports</a></li> -->

</ul>
</div>

<div class="container">

<div class="card">

<div class="card-header">

<h2>Select a Course</h2>

</div>

<div class="card-body">

<?php if(count($courses)>0): ?>

<table>

<thead>

<tr>

<th>Course Code</th>
<th>Course Title</th>
<th>Lecturer</th>
<th>Total Sessions</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php foreach($courses as $course): ?>

<tr>

<td><?php echo htmlspecialchars($course['course_code']); ?></td>

<td><?php echo htmlspecialchars($course['course_title']); ?></td>

<td>
<?php
echo !empty($course['lecturer_name'])
? htmlspecialchars($course['lecturer_name'])
: "Not Assigned";
?>
</td>

<td><?php echo $course['total_sessions']; ?></td>

<td>

<a class="btn"
href="hod_generate_eligibility.php?course_id=<?php echo $course['id']; ?>">

Generate

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<?php else: ?>

<div class="no-data">

No courses found in your department.

</div>

<?php endif; ?>

<a href="hod_dashboard.php" class="back-btn">

← Back to Dashboard

</a>

</div>

</div>

</div>

</body>

</html>