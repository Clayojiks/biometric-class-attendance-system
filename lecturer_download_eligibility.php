<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'lecturer') {
    header("Location: login.php");
    exit();
}

$db = new SQLite3("attendance.db");

$lecturer_department = $_SESSION['department'];
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if ($course_id == 0) {
    die("Invalid course.");
}

// Get course information
$stmt = $db->prepare("
SELECT *
FROM courses
WHERE id = :cid
AND lecturer_id = :lecturer_id
");

$stmt->bindValue(':cid', $course_id, SQLITE3_INTEGER);
$stmt->bindValue(':lecturer_id', $_SESSION['user_id'], SQLITE3_INTEGER);

$course = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$course) {
    die("Course not found.");
}

// Get attendance report
$stmt = $db->prepare("
SELECT
    s.reg_no,
    s.name,

    COUNT(
        CASE
        WHEN a.status='present'
        THEN 1
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

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="'.$course['course_code'].'_Attendance_Report.csv"');

$output = fopen("php://output","w");

fputcsv($output,[
    "Registration Number",
    "Student Name",
    "Classes Attended",
    "Total Sessions",
    "Attendance %",
    "Eligibility"
]);

while($row=$result->fetchArray(SQLITE3_ASSOC))
{

    $attended=$row['attended'];

    $total=$course['total_sessions'];

    $percentage=$total>0 ? round(($attended/$total)*100):0;

    $status=$percentage>=75 ? "Eligible":"Ineligible";

    fputcsv($output,[

        $row['reg_no'],
        $row['name'],
        $attended,
        $total,
        $percentage."%",
        $status

    ]);

}

fclose($output);
exit();
?>