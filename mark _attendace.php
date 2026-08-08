<?php
// mark_attendance.php
// Receives attendance data from fingerprint scanner (hardware or simulation)
// Used by: test_attendance.html, Arduino Python bridge, and future hardware

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Connect to database
$db = new SQLite3(__DIR__ . '/../attendance.db');

if (!$db) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get the JSON input from the request
$input = json_decode(file_get_contents('php://input'), true);

// If no JSON, try POST form data (alternative)
if (!$input) {
    $input = $_POST;
}

// Extract data with defaults
$student_id = isset($input['student_id']) ? intval($input['student_id']) : 0;
$fingerprint_id = isset($input['fingerprint_id']) ? intval($input['fingerprint_id']) : 0;
$course_id = isset($input['course_id']) ? intval($input['course_id']) : 0;
$date = isset($input['date']) ? $input['date'] : date('Y-m-d');
$time = isset($input['time']) ? $input['time'] : date('H:i:s');
$week = isset($input['week']) ? intval($input['week']) : date('W') - 35; // Adjust based on semester
$status = isset($input['status']) ? $input['status'] : 'present';
$method = isset($input['method']) ? $input['method'] : 'biometric';

// If fingerprint_id provided but student_id is missing, look up the student
if ($fingerprint_id > 0 && $student_id == 0) {
    $stmt = $db->prepare("SELECT id FROM students WHERE fingerprint_id = :fid");
    $stmt->bindValue(':fid', $fingerprint_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $student = $result->fetchArray(SQLITE3_ASSOC);
    if ($student) {
        $student_id = $student['id'];
    }
}

// Validate required fields
if ($student_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Student ID is required']);
    exit();
}

if ($course_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Course ID is required']);
    exit();
}

// Check if attendance already recorded for this student, course, and date (prevent duplicates)
$stmt = $db->prepare("SELECT id FROM attendance WHERE student_id = :sid AND course_id = :cid AND date = :date");
$stmt->bindValue(':sid', $student_id, SQLITE3_INTEGER);
$stmt->bindValue(':cid', $course_id, SQLITE3_INTEGER);
$stmt->bindValue(':date', $date, SQLITE3_TEXT);
$existing = $stmt->execute();
if ($existing->fetchArray()) {
    echo json_encode(['success' => false, 'message' => 'Attendance already recorded for today']);
    exit();
}

// Insert attendance record
$stmt = $db->prepare("INSERT INTO attendance (student_id, course_id, week, date, time, status, method) 
                      VALUES (:sid, :cid, :week, :date, :time, :status, :method)");

$stmt->bindValue(':sid', $student_id, SQLITE3_INTEGER);
$stmt->bindValue(':cid', $course_id, SQLITE3_INTEGER);
$stmt->bindValue(':week', $week, SQLITE3_INTEGER);
$stmt->bindValue(':date', $date, SQLITE3_TEXT);
$stmt->bindValue(':time', $time, SQLITE3_TEXT);
$stmt->bindValue(':status', $status, SQLITE3_TEXT);
$stmt->bindValue(':method', $method, SQLITE3_TEXT);

$result = $stmt->execute();

if ($result) {
    // Get student name for response
    $stmt2 = $db->prepare("SELECT name, reg_no FROM students WHERE id = :sid");
    $stmt2->bindValue(':sid', $student_id, SQLITE3_INTEGER);
    $result2 = $stmt2->execute();
    $student_info = $result2->fetchArray(SQLITE3_ASSOC);
    
    // Get course info for response
    $stmt3 = $db->prepare("SELECT course_code, course_title FROM courses WHERE id = :cid");
    $stmt3->bindValue(':cid', $course_id, SQLITE3_INTEGER);
    $result3 = $stmt3->execute();
    $course_info = $result3->fetchArray(SQLITE3_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Attendance recorded successfully',
        'data' => [
            'student_id' => $student_id,
            'student_name' => $student_info['name'] ?? 'Unknown',
            'reg_no' => $student_info['reg_no'] ?? 'Unknown',
            'course_id' => $course_id,
            'course_code' => $course_info['course_code'] ?? 'Unknown',
            'course_title' => $course_info['course_title'] ?? 'Unknown',
            'date' => $date,
            'time' => $time,
            'week' => $week,
            'status' => $status,
            'method' => $method
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to record attendance']);
}

$db->close();
?>