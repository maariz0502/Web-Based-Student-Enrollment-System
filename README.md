# CMSC4003_Web-Based-Student-Enrollment-System

This project involves designing and implementing a Web-Based Student Enrollment Information Management System that allows students and administrators to browse, enroll, and manage enrollment data. The system provides various functionalities, including student personal and academic information management, course enrollment with prerequisite and availability checks, and administrative operations such as student registration, updating, and grade management. Additionally, concurrency control mechanisms must be implemented to maintain data integrity when multiple users interact with the system simultaneously.

Database Concepts:

Relational Database Schema:
-Defining SQL tables for students, courses, sections, enrollment, and grades.
-Implementing Primary Keys (PK), Foreign Keys (FK), and Constraints to ensure data consistency.

Stored Procedures:
-Creating stored procedures for operations like student registration, enrollment validation, and GPA calculation.

Views:
-Implementing database views to simplify data retrieval for student information and academic records.

Triggers:
-Writing triggers to automatically update probation status when a student's GPA falls below 2.0.

Concurrency Control:
-Implementing mechanisms to prevent race conditions during student ID generation and enrollment processing.
-Using transactions and locking mechanisms to ensure data integrity.


PHP and Web Development

User Authentication:
-Implementing student and admin login functionality using session management in PHP.

Dynamic Web Pages:
-Creating PHP-based pages to display student information, available courses, and enrollment results dynamically.

Form Handling & Validation:
-Ensuring secure and valid data input for student registration, course enrollment, and grade submission.

Admin Panel:
-Providing a web-based interface for administrators to add, update, and delete students and manage grades.

Error Handling & Notifications:
-Displaying meaningful error messages for failed enrollments due to prerequisites, deadlines, or seat availability.
