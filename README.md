# Biometric Class Attendance Management System

## Overview

The Biometric Class Attendance Management System is a web-based attendance management solution developed as a final year project for Multimedia University of Kenya (MMU).

The system is designed to improve the accuracy, accountability, and efficiency of classroom attendance management by combining biometric identity verification with automated attendance recording, attendance percentage calculation, and examination eligibility reporting.

The project focuses on addressing challenges associated with manual attendance systems, including proxy attendance ("buddy punching"), time wastage during roll calls, inaccurate records, and difficulties in determining student eligibility for examinations.

## Problem Addressed

Traditional attendance methods such as paper-based registers and manual roll calls can be time-consuming and vulnerable to impersonation and record-keeping errors.

The system addresses these challenges by providing an automated attendance management platform with biometric verification, real-time attendance tracking, and automated calculation of attendance percentages and examination eligibility.

## Objectives

### Main Objective

To design a Biometric-Based Class Attendance Management System.

### Specific Objectives

- To design and develop a web-based login and registration system for students and lecturers.
- To implement an automated student identity verification mechanism using fingerprint recognition logic.
- To automatically calculate student attendance percentages and determine the 75% examination eligibility requirement.
- To implement dashboards and reporting features for monitoring attendance and student engagement.

## Key Features

### Student Management
- Student registration and authentication.
- Student profile management.
- Course enrollment and course allocation.
- Attendance history and attendance percentage viewing.

### Lecturer Management
- Lecturer authentication.
- Course management.
- Attendance monitoring.
- Attendance reports.
- Resolve course disputes.
- Student attendance and eligibility monitoring.

### HOD / Administrative Management
- Course management.
- Student and lecturer information management.
- Attendance monitoring.
- Examination eligibility reports.
- Attendance report generation.
- Course disputes viewing.

### Biometric Attendance
- Fingerprint enrollment and verification workflow.
- Automated student identity verification.
- Attendance recording based on verified identity.
- Timestamping of attendance records.

### Attendance and Eligibility
- Automatic attendance percentage calculation.
- Course-based attendance tracking.
- Automatic determination of the 75% examination eligibility requirement.
- Eligibility reports for academic administration.

### Reporting and Analytics
- Attendance monitoring dashboards.
- Student attendance information.
- Examination eligibility reports.
- Attendance data visualization and reporting.

## Technology Stack

### Software

- PHP
- SQLite3
- HTML5
- CSS3
- JavaScript
- XAMPP
- Visual Studio Code
- Arduino IDE
- Python Bridge

### Hardware

- Arduino Uno R3
- AS608 fingerprint sensor
- DS3231 Real-Time Clock (RTC)
- 16x2 LCD with I2C
- Buzzer
- 4 Push buttons

## System Architecture

The system combines a web-based application with an Arduino-based biometric hardware interface.

The Arduino handles communication with the fingerprint sensor and other hardware components. The fingerprint verification process provides an identity signal that can be processed by the application for attendance recording.

The DS3231 RTC provides date and time information, while the LCD provides feedback to the user during the attendance process.

The web application manages students, lecturers, courses, attendance records, attendance percentages, and examination eligibility.

## Hardware Communication

The Arduino Uno communicates with the AS608 fingerprint sensor using serial communication.

The DS3231 RTC and 16x2 LCD use the I2C communication interface.

The documented hardware configuration includes:

| Arduino Pin | Component | Purpose |
|---|---|---|
| 5V | Hardware modules | Main power supply |
| GND | Hardware modules | Common ground |
| pin 4 | button 1 | Enrollment Mode |
| pin 5 | button 2 | Start/Stop attendance |
| pin 6 | button 3 | Next course |
| pin 7 | button 4 | Previous course |
| Pin 8 | Passive buzzer | Audio feedback |
| Pin 12 (RX) | AS608 TX | Receive sensor data |
| Pin 11 (TX) | AS608 RX | Send data to sensor |
| A4 (SDA) | LCD & RTC SDA | I2C data |
| A5 (SCL) | LCD & RTC SCL | I2C clock |


## Attendance Workflow

## Attendance Workflow

```text
Student
   |
   v
Fingerprint Verification
   |
   v
Identity Verification
   |
   v
Attendance Recording
   |
   v
Timestamp
   |
   v
Database
   |
   v
Attendance Dashboard
   |
   v
Attendance Percentage
   |
   v
75% Examination Eligibility
```

## Project Structure

The project uses separate folders for JavaScript and CSS resources while the main application functionality is implemented through PHP files.

```text
biometric_attendance/
│
├── css/
│   └── CSS files
│
├── JS/
│   └── JavaScript files
│
├── PHP application files
│
├── attendance.sqlppro
│
├── .gitignore
│
└── README.md
```

The project intentionally retains the original PHP, JavaScript, and CSS file naming and structure used during development.

## Running the System Locally

### Requirements

- XAMPP
- Apache
- PHP with SQLite3 support
- Web browser
- The project source code

### Installation

1. Install XAMPP.
2. Copy the `biometric_attendance` project folder into:

```text
C:\xampp\htdocs\
```

3. Start Apache from the XAMPP Control Panel.
4. Ensure SQLite3 support is enabled in PHP.
5. Open the application through a web browser using the appropriate PHP entry page.

Example:

```text
http://localhost/biometric_attendance/
```

If the project uses a specific login page, open the corresponding PHP login page.

## Database

The system uses SQLite3 for local database storage.

The database contains information required for:

- Users
- Students
- Lecturers
- Courses
- Enrollments
- Attendance
- Examination eligibility

The live/local database is excluded from this public repository to avoid exposing potentially sensitive student or institutional data.

## Security and Privacy

The public repository does not contain the local attendance database.

Real usernames, passwords, and other sensitive credentials should not be published in the repository.

For demonstration purposes, the application can be run in a local XAMPP environment using appropriate test or demonstration accounts.

## Testing

The project included testing of major system functions including:

- User authentication
- Attendance calculation
- Examination eligibility determination
- Database operations
- System functionality
- Security-related checks

The documented testing results reported successful functional test cases, with page loading, database query, and fingerprint authentication performance evaluated during system testing.

## Limitations

- The system is primarily designed for local deployment.
- The public repository does not include the live attendance database.
- Full production deployment would require additional security and infrastructure considerations.
- Further hardware integration and testing can be performed as part of future development.

## Future Improvements

Potential future improvements include:

- Stronger password encryption and security mechanisms
- Email notifications
- Mobile application development
- Expanded hardware integration testing
- Further deployment and scalability improvements
- Additional biometric authentication capabilities

## Project Documentation

The project was developed and documented as a final year project focusing on biometric attendance management within the Multimedia University of Kenya context.

## Author

**Lucy Clay**

Bachelor of Information Technology  
Multimedia University of Kenya

## License

This project is an academic final year project developed by Lucy Clay at Multimedia University of Kenya.

The source code is provided for viewing and educational reference purposes only. No permission is granted to copy, modify, distribute, reproduce, or use this project or substantial portions of its source code without prior written permission from the author.