<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hod') {
    header("Location: login.php");
    exit();
}

$db = new SQLite3("attendance.db");

$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
$course_id  = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$department = $_SESSION['department'];

if($student_id == 0 || $course_id == 0){
    die("Invalid request.");
}


//get student and course details

$stmt = $db->prepare("
SELECT
    s.*,
    c.course_code,
    c.course_title,
    c.total_sessions
FROM students s
JOIN enrollment e
    ON s.id = e.student_id
JOIN courses c
    ON c.id = e.course_id
WHERE
    s.id = :student
AND c.id = :course
AND c.department = :dept
");

$stmt->bindValue(":student",$student_id,SQLITE3_INTEGER);
$stmt->bindValue(":course",$course_id,SQLITE3_INTEGER);
$stmt->bindValue(":dept",$department,SQLITE3_TEXT);

$student = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if(!$student){
    die("Student not found.");
}

//get student attendance records for the course

$stmt = $db->prepare("
SELECT
    week,
    date,
    time,
    status,
    method
FROM attendance
WHERE student_id=:student
AND course_id=:course
ORDER BY week
");

$stmt->bindValue(":student",$student_id,SQLITE3_INTEGER);
$stmt->bindValue(":course",$course_id,SQLITE3_INTEGER);

$result = $stmt->execute();

$attendance=[];

$present=0;

while($row=$result->fetchArray(SQLITE3_ASSOC))
{
    $attendance[]=$row;

    if($row['status']=="present"){
        $present++;
    }
}

$total_sessions=$student['total_sessions'];

$percentage=$total_sessions>0
? round(($present/$total_sessions)*100)
:0;

?>
<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Student Attendance</title>

<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/hod_student_detail.css">

</head>

<body>

<div class="dashboard-header">

<div>

<h1>BIOMETRIC ATTENDANCE SYSTEM</h1>

<p>HOD Student Attendance</p>

</div>

<div class="user-info">

Welcome <?php echo htmlspecialchars($_SESSION['user_name']); ?>

<a href="logout.php" class="logout-btn">Logout</a>

</div>

</div>

<div class="container">

<div class="overall-card">

<h2><?php echo htmlspecialchars($student['name']); ?></h2>

<p><strong>Registration No:</strong> <?php echo htmlspecialchars($student['reg_no']); ?></p>

<p><strong>Program:</strong> <?php echo htmlspecialchars($student['program']); ?></p>

<p><strong>Year:</strong> <?php echo $student['year']; ?></p>

<p><strong>Fingerprint ID:</strong>

<?php

echo !empty($student['fingerprint_id'])

? $student['fingerprint_id']

: "Not Enrolled";

?>

</p>

<hr>

<p><strong>Course:</strong>

<?php echo htmlspecialchars($student['course_code']); ?>

-

<?php echo htmlspecialchars($student['course_title']); ?>

</p>

<p><strong>Attendance:</strong>

<?php echo $present; ?>

/

<?php echo $total_sessions; ?>

</p>

<p><strong>Percentage:</strong>

<?php echo $percentage; ?>%

</p>

<p>

<strong>Status:</strong>

<?php

if($percentage>=75){

echo "<span class='eligible'>✅ Eligible</span>";

}else{

echo "<span class='ineligible'>❌ Not Eligible</span>";

}

?>

</p>

</div>

<div class="card">

<div class="card-header">

<h3>Attendance Records</h3>

</div>

<div class="card-body">

<table class="data-table">

<thead>

   <tr>

<th>Week</th>

<th>Date</th>

<th>Time Logged</th>

<th>Method</th>

<th>Status</th>

</tr>

</thead>

<tbody>

<?php

if(count($attendance)>0){

foreach($attendance as $row){

?>

<tr>

<td>Week <?php echo $row['week']; ?></td>

<td><?php echo htmlspecialchars($row['date']); ?></td>

<td><?php echo htmlspecialchars($row['time']); ?></td>

<td><?php echo ucfirst($row['method']); ?></td>

<td>

<?php

if($row['status']=="present"){

echo "<span class='eligible'>✅ Present</span>";

}else{

echo "<span class='ineligible'>❌ Absent</span>";

}

?>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="5" style="text-align:center;">

No attendance records found.

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>

<br>

<a href="javascript:history.back()" class="logout-btn">← Back</a>

</div>

</body>

</html>