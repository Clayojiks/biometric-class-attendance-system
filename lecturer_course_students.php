<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'lecturer') {
    header("Location: login.php");
    exit();
}

$db = new SQLite3("attendance.db");

$lecturer_id = $_SESSION['user_id'];

$course_id = isset($_GET['course_id'])
    ? intval($_GET['course_id'])
    : 0;

if ($course_id == 0) {
    die("Invalid course.");
}

/* Verify lecturer owns course */

$stmt = $db->prepare("
SELECT *
FROM courses
WHERE id = :course
AND lecturer_id = :lecturer
");

$stmt->bindValue(":course",$course_id,SQLITE3_INTEGER);
$stmt->bindValue(":lecturer",$lecturer_id,SQLITE3_INTEGER);

$result = $stmt->execute();

$course = $result->fetchArray(SQLITE3_ASSOC);

if(!$course){
    die("Course not found or not assigned to you.");
}

/* Students */

$stmt = $db->prepare("
SELECT
    s.reg_no,
    s.name,
    s.program,
    s.year

FROM students s

INNER JOIN enrollment e
ON s.id = e.student_id

WHERE e.course_id = :course

ORDER BY s.name
");

$stmt->bindValue(":course",$course_id,SQLITE3_INTEGER);

$result = $stmt->execute();
?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Course Students</title>

<link rel="stylesheet" href="css/style.css">

<style>

.container{

width:90%;
margin:auto;
margin-top:30px;

}

table{

width:100%;
border-collapse:collapse;

}

th{

background:#8B0000;
color:white;
padding:10px;

}

td{

padding:10px;
border:1px solid #ddd;

}

tr:nth-child(even){

background:#f5f5f5;

}

.back{

display:inline-block;
margin-top:20px;
padding:10px 20px;
background:#8B0000;
color:white;
text-decoration:none;

}

</style>

</head>

<body>

<div class="container">

<h2>

Students Enrolled

</h2>

<h3>

<?php
echo htmlspecialchars($course['course_code']);
?>

-

<?php
echo htmlspecialchars($course['course_title']);
?>

</h3>

<table>

<tr>

<th>#</th>

<th>Registration Number</th>

<th>Name</th>

<th>Program</th>

<th>Year</th>

</tr>

<?php

$i=1;

while($row=$result->fetchArray(SQLITE3_ASSOC))
{

?>

<tr>

<td><?php echo $i++; ?></td>

<td><?php echo htmlspecialchars($row['reg_no']); ?></td>

<td><?php echo htmlspecialchars($row['name']); ?></td>

<td><?php echo htmlspecialchars($row['program']); ?></td>

<td><?php echo htmlspecialchars($row['year']); ?></td>

</tr>

<?php

}

?>

</table>

<a class="back" href="lecturer_courses.php">

← Back to My Courses

</a>

</div>

</body>

</html>