<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hod') {
	header("Location: login.php");
	exit();
}

$db = new SQLite3('attendance.db');

$hod_department = $_SESSION['department'];
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if ($course_id == 0) {
	die("Invalid course.");
}

// Get course information
$stmt = $db->prepare("
SELECT c.*, l.name AS lecturer_name
FROM courses c
LEFT JOIN lecturers l ON c.lecturer_id = l.id
WHERE c.id = :cid
AND c.department = :dept
");

$stmt->bindValue(':cid', $course_id, SQLITE3_INTEGER);
$stmt->bindValue(':dept', $hod_department, SQLITE3_TEXT);

$course = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$course) {
	die("Course not found.");
}

// Get students
$stmt = $db->prepare("
SELECT
	s.id,
	s.reg_no,
	s.name,

	COUNT(
		CASE
				WHEN a.status='present' THEN 1
		END
	) AS attended

FROM students s

JOIN enrollment e
ON s.id=e.student_id

LEFT JOIN attendance a
ON a.student_id=s.id
AND a.course_id=e.course_id

WHERE e.course_id=:course

GROUP BY s.id

ORDER BY s.name
");

$stmt->bindValue(':course', $course_id, SQLITE3_INTEGER);

$result = $stmt->execute();

$eligible = [];
$ineligible = [];

while($row = $result->fetchArray(SQLITE3_ASSOC)){

	$row['attended'] = $row['attended'] ?? 0;

	$row['percentage'] =
		$course['total_sessions'] > 0
		? round(($row['attended']/$course['total_sessions'])*100)
		: 0;

	if($row['percentage'] >= 75){
		$eligible[] = $row;
	}else{
		$ineligible[] = $row;
	}
}
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Eligibility List</title>

<link rel="stylesheet" href="css/style.css">

<style>

body{
	font-family:Arial;
	background:#f4f6f9;
}

.container{
	width:90%;
	margin:auto;
	background:#fff;
	padding:20px;
}

table{
	width:100%;
	border-collapse:collapse;
	margin-bottom:30px;
}

th,td{
	border:1px solid #ddd;
	padding:10px;
}

th{
	background:#003366;
	color:white;
}

.eligible{
	color:green;
	font-weight:bold;
}

.ineligible{
	color:red;
	font-weight:bold;
}

.print-btn{
	background:#003366;
	color:white;
	padding:10px 20px;
	border:none;
	cursor:pointer;
}

</style>

</head>

<body>

<div class="container">

<h2>Examination Eligibility List</h2>

<p>

<strong>Course:</strong>

<?php echo htmlspecialchars($course['course_code']); ?>

-

<?php echo htmlspecialchars($course['course_title']); ?>

</p>

<p>

<strong>Lecturer:</strong>

<?php echo htmlspecialchars($course['lecturer_name'] ?? "Not Assigned"); ?>

</p>

<p>

<strong>Total Sessions:</strong>

<?php echo $course['total_sessions']; ?>

</p>

<h3 class="eligible">

Eligible Students (75% and Above)

</h3>

<table>

<tr>

<th>#</th>

<th>Reg No</th>

<th>Name</th>

<th>Attended</th>

<th>Percentage</th>

</tr>

<?php

$i=1;

foreach($eligible as $student){

?>

<tr>

<td><?= $i++; ?></td>

<td><?= htmlspecialchars($student['reg_no']); ?></td>

<td><?= htmlspecialchars($student['name']); ?></td>

<td><?= $student['attended']; ?>/<?= $course['total_sessions']; ?></td>

<td><?= $student['percentage']; ?>%</td>

</tr>

<?php } ?>

</table>

<h3 class="ineligible">

Ineligible Students (Below 75%)

</h3>

<table>

<tr>

<th>#</th>

<th>Reg No</th>

<th>Name</th>

<th>Attended</th>

<th>Percentage</th>

</tr>

<?php

$i=1;

foreach($ineligible as $student){

?>

<tr>

<td><?= $i++; ?></td>

<td><?= htmlspecialchars($student['reg_no']); ?></td>

<td><?= htmlspecialchars($student['name']); ?></td>

<td><?= $student['attended']; ?>/<?= $course['total_sessions']; ?></td>

<td><?= $student['percentage']; ?>%</td>

</tr>

<?php } ?>

</table>

<button class="print-btn" onclick="window.print()">

🖨 Print / Save PDF

</button>

<br><br>

<a href="hod_eligibility_courses.php">

← Back to Courses

</a>

</div>

</body>

</html>