<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hod') {
    header("Location: login.php");
    exit();
}

$db = new SQLite3("attendance.db");

$department = $_SESSION['department'];

$stmt = $db->prepare("
SELECT
    id,
    course_code,
    course_title
FROM courses
WHERE department = :dept
ORDER BY course_code
");

$stmt->bindValue(":dept",$department,SQLITE3_TEXT);

$result = $stmt->execute();

$courses=[];

while($row=$result->fetchArray(SQLITE3_ASSOC)){
    $courses[]=$row;
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Generate Eligibility</title>

<link rel="stylesheet" href="css/style.css">

</head>

<body>

<div class="container">

<h2>Select a Course</h2>

<?php if(count($courses)>0): ?>

<table class="data-table">

<thead>

<tr>

<th>Course Code</th>

<th>Course Title</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php foreach($courses as $course): ?>

<tr>

<td><?php echo htmlspecialchars($course['course_code']); ?></td>

<td><?php echo htmlspecialchars($course['course_title']); ?></td>

<td>

<a class="btn btn-primary"
href="hod_eligibility_report.php?course_id=<?php echo $course['id']; ?>">

Generate List

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<?php else: ?>

<p>No courses found.</p>

<?php endif; ?>

<br>

<a href="hod_dashboard.php">← Back to Dashboard</a>

</div>

</body>

</html>