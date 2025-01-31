-- drop old tables
drop table Client cascade constraints;
drop table ClientSession cascade constraints;
drop table Student cascade constraints;
drop table Admin cascade constraints;
drop table Course cascade constraints;
drop table Section cascade constraints;
drop table Taking cascade constraints;
drop table Prerequisite cascade constraints;
drop table counter_table;
drop PROCEDURE add_student_using_counter;
drop VIEW StudentAcademicInfo;
drop trigger PreventFullClassEnrollment;
drop table temptaken;
drop trigger EnforceCourseTakenCheck;
drop trigger Maintaintemptaken;
drop TRIGGER UpdateProbationStatus;
drop trigger EnforceSectionDeadlineCheck;
drop trigger EnforcePrerequisitesCheck;


create table Client(		-- same as User in EER diagram
	username varchar2(20) PRIMARY KEY,
	password varchar2(20) NOT NULL,
	firstName varchar2(20),
	lastName varchar2(20)
);

create table ClientSession(	-- same as User session in EER diagram
    sessionId varchar2(32) PRIMARY KEY,
    sessionDate date,
    username varchar2(20) NOT NULL,
    FOREIGN KEY (username) REFERENCES Client(username) ON DELETE CASCADE
);

create table Admin(
	adminId varchar2(9) PRIMARY KEY,
	startDate date,
	username varchar2(20) NOT NULL,
	FOREIGN KEY (username) REFERENCES Client(username) ON DELETE CASCADE
);

create table Student(
	studentId varchar2(8) PRIMARY KEY,
	age number(2),
	address varchar(40),
	admissiondate date,
	studentType CHAR(1) NOT NULL CHECK (studentType IN ('u', 'g')), -- 'u' for undergrad, 'g' for graduate,	
	standing varchar2(15),
	concentration varchar2(15),
	status varchar2(20),
	username varchar2(20) NOT NULL,
	FOREIGN KEY (username) REFERENCES Client(username) ON DELETE CASCADE
);

create table Course(
	courseNumber varchar2(9) PRIMARY KEY,
	title varchar2(50) NOT NULL,
	credithour number(1) NOT NULL
);

create table Section(
	sectionId varchar2(9) PRIMARY KEY,
	deadline date,
	capacity number(3),
	semesterYear number(4),
	semesterSeason varchar2(7),
	dateTime varchar2(11),		-- HH:MM AM/PM
	dateDays varchar2(7),		-- MTWRF, any combination
	courseNumber varchar2(9),
    currentSize number(3),
	FOREIGN KEY (courseNumber) REFERENCES Course(courseNumber) ON DELETE CASCADE
);

create table Taking(  -- same as Enrolls in EER diagram
	sectionId varchar2(9),
	studentId varchar2(8),
	grade number(1),	-- grade can be 4,3,2,1
	PRIMARY KEY (sectionId, studentId),
	FOREIGN KEY (sectionId) REFERENCES Section(sectionId) ON DELETE CASCADE,
	FOREIGN KEY (studentId) REFERENCES Student(studentId) ON DELETE CASCADE
);

create table Prerequisite(
	prerequisiteCourseNumber varchar2(9),
	mainCourseNumber varchar2(9),
	PRIMARY KEY (prerequisiteCourseNumber, mainCourseNumber),
	FOREIGN KEY (prerequisiteCourseNumber) REFERENCES Course(courseNumber) ON DELETE CASCADE,
	FOREIGN KEY (mainCourseNumber) REFERENCES Course(courseNumber) ON DELETE CASCADE
);


-- ADDING STUDENTS USING STORED PROCEDURE
create table counter_table (
  counter number(6) primary key
);

CREATE OR REPLACE PROCEDURE add_student_using_counter (
    -- input parameters for student and client details
    p_username IN VARCHAR2,
    p_password IN VARCHAR2,
    p_firstName IN VARCHAR2 DEFAULT NULL,
    p_lastName IN VARCHAR2 DEFAULT NULL,
    p_age IN NUMBER DEFAULT NULL,
    p_address IN VARCHAR2 DEFAULT NULL,
    p_admissiondate IN DATE DEFAULT NULL,
    p_studentType IN CHAR,
    p_standing IN VARCHAR2 DEFAULT NULL,
    p_concentration IN VARCHAR2 DEFAULT NULL
) AS
    row_counter NUMBER;
    current_counter NUMBER(6);
    v_student_id VARCHAR2(8);
BEGIN

    SELECT count(*) INTO row_counter FROM counter_table;

    IF row_counter > 0 THEN
        SELECT MAX(counter) INTO current_counter FROM counter_table;
        current_counter := current_counter + 1;
    ELSE
        current_counter := 1;
    END IF;

  loop
    begin
      insert into counter_table values (current_counter);
      -- If we reach here, we have got a new valid counter value
      DBMS_OUTPUT.PUT_LINE('Trying to insert counter: ' || current_counter); 
      commit;
      exit;
    exception
      when DUP_VAL_ON_INDEX then
        -- Simply increase the counter by 1 and try again.
        current_counter := current_counter + 1;
    end;
  end loop;

    -- Generate a student ID (e.g., 'SA000001', 'SA000002', etc.)
	v_student_id := UPPER(SUBSTR(p_firstName, 1, 1) || SUBSTR(p_lastName, 1, 1) || LPAD(current_counter, 6, '0'));

    -- Insert a new record into the Client table
    INSERT INTO Client (
        username, 
        password, 
        firstName, 
        lastName
    ) VALUES (
        p_username, 
        p_password, 
        p_firstName, 
        p_lastName
    );

    -- Insert a new record into the Student table
    INSERT INTO Student (
        studentId, 
        age, 
        address, 
        admissiondate, 
        studentType, 
        standing, 
        concentration, 
        status, 
        username
    ) VALUES (
        v_student_id, 
        p_age, 
        p_address, 
        p_admissiondate, 
        p_studentType, 
        p_standing, 
        p_concentration, 
        'Not Established', 
        p_username
    );

    COMMIT;
    
END;
/


-- UPDATE CURRENTSIZE IN SECTION
CREATE OR REPLACE TRIGGER CurrentSizeControl
AFTER INSERT OR UPDATE ON Taking
FOR EACH ROW
DECLARE
    v_current_size NUMBER;
BEGIN
    -- Check if this is an INSERT or UPDATE action
    IF INSERTING THEN
        -- Update the current size of the section for a new enrollment
        UPDATE Section
        SET currentSize = currentSize + 1
        WHERE sectionId = :NEW.sectionId;
    ELSIF UPDATING THEN
        -- Optional: Handle special cases for updates if needed
        -- For example, if the section ID is updated, decrement the old section's size
        IF :OLD.sectionId != :NEW.sectionId THEN
            -- Decrement the old section size
            UPDATE Section
            SET currentSize = currentSize - 1
            WHERE sectionId = :OLD.sectionId;

            -- Increment the new section size
            UPDATE Section
            SET currentSize = currentSize + 1
            WHERE sectionId = :NEW.sectionId;
        END IF;
    END IF;
END;
/

-- PREVENT ENROLLMENT WHEN CLASS FULL
CREATE OR REPLACE TRIGGER PreventFullClassEnrollment
BEFORE INSERT ON Taking
FOR EACH ROW
DECLARE
    v_current_size NUMBER;
    v_capacity NUMBER;
BEGIN
    -- Get the current size and capacity of the section
    SELECT currentSize, capacity
    INTO v_current_size, v_capacity
    FROM Section
    WHERE sectionId = :NEW.sectionId;

    -- Check if the class is full
    IF v_current_size >= v_capacity THEN
        -- Raise an application error if the class is full
        RAISE_APPLICATION_ERROR(-20001, 'Class is full');
    END IF;
END;
/

-- CHECKS IF STUDENT HAS TAKEN COURSE BEFORE
-- Temporary table that is used to hold courses students have taken
CREATE TABLE TempTaken (   
  studentid varchar2(8), 
  coursenumber varchar2(9),
  grade number(1)
);
-- Makes sure temptaken is in sync with taking
-- Corrected Trigger to Maintain TempTaken
CREATE OR REPLACE TRIGGER MaintainTempTaken
AFTER INSERT OR DELETE OR UPDATE OF sectionId, studentId, grade ON Taking
FOR EACH ROW
BEGIN
    -- Handle insert into Taking
    IF INSERTING THEN 
        INSERT INTO TempTaken (studentid, coursenumber, grade)
        VALUES (
            :NEW.studentid, 
            (SELECT s.coursenumber FROM Section s WHERE s.sectionid = :NEW.sectionid), 
            :NEW.grade
        );
    -- Handle delete from Taking
    ELSIF DELETING THEN
        DELETE FROM TempTaken
        WHERE studentid = :OLD.studentid
          AND coursenumber = (
              SELECT s.coursenumber FROM Section s WHERE s.sectionid = :OLD.sectionid
          );

    -- Handle update in Taking
    ELSIF UPDATING THEN
        -- Remove old data
        DELETE FROM TempTaken
        WHERE studentid = :OLD.studentid
          AND coursenumber = (
              SELECT s.coursenumber FROM Section s WHERE s.sectionid = :OLD.sectionid
          );

        -- Insert updated data
        INSERT INTO TempTaken (studentid, coursenumber, grade)
        VALUES (
            :NEW.studentid, 
            (SELECT s.coursenumber FROM Section s WHERE s.sectionid = :NEW.sectionid), 
            :NEW.grade
        );
    END IF;
END;
/

-- CALCULATING GPA AND OTHER ACADEMIC INFO USING VIEW
CREATE OR REPLACE VIEW StudentAcademicInfo AS
SELECT 
    s.studentId,
    c.username,
    -- Calculate the number of courses completed
    COUNT(DISTINCT CASE WHEN t.grade IS NOT NULL THEN t.courseNumber END) AS courses_completed,
    -- Calculate total credit hours earned
    SUM(CASE WHEN t.grade IS NOT NULL THEN co.credithour ELSE 0 END) AS total_credits,
    -- Calculate GPA using the formula
    ROUND(
        SUM(CASE WHEN t.grade IS NOT NULL THEN t.grade * co.credithour ELSE 0 END) / 
        NULLIF(SUM(CASE WHEN t.grade IS NOT NULL THEN co.credithour ELSE 0 END), 0),
        2
    ) AS GPA
    FROM Student s
    JOIN Client c ON s.username = c.username
    LEFT JOIN temptaken t ON s.studentId = t.studentId
    LEFT JOIN Course co ON t.courseNumber = co.courseNumber
    GROUP BY 
    s.studentId, c.username;


-- Raises error if course is already taken
CREATE OR REPLACE TRIGGER EnforceCourseTakenCheck
BEFORE INSERT ON Taking
FOR EACH ROW
DECLARE
  v_count NUMBER;
BEGIN
  -- Check if the student has already taken the course
  SELECT COUNT(*)
  INTO v_count
  FROM temptaken
  WHERE studentid = :NEW.studentid
  AND coursenumber = (
    SELECT s.coursenumber
    FROM Section s
    WHERE s.sectionid = :NEW.sectionid
  );

  -- Raise an error if the course has been taken 
  IF v_count > 0 THEN
    RAISE_APPLICATION_ERROR(-20002, 'Student has already taken this course.');
  END IF;
END;
/

-- Raises an error if the student tries to enroll into a section past its deadline
CREATE OR REPLACE TRIGGER EnforceSectionDeadlineCheck
BEFORE INSERT ON Taking
FOR EACH ROW
DECLARE
  v_deadline date;
BEGIN
  -- Check if the student tries to take section past deadline
  SELECT section.deadline
  INTO v_deadline
  FROM section
  WHERE sectionId = :NEW.sectionId;

  -- Raise an error if the course has been taken 
  IF sysdate > v_deadline THEN
    RAISE_APPLICATION_ERROR(-20003, 'This sections deadline has passed.');
  END IF;
END;
/

-- Raises an error if student tries to enroll into a section without taking prerequisite(s)
CREATE OR REPLACE TRIGGER EnforcePrerequisitesCheck
BEFORE INSERT ON Taking -- Trigger for ensuring prerequisites are met
FOR EACH ROW
DECLARE
    v_prereqs_taken NUMBER := 0; -- Number of prerequisites taken by the student
    v_num_of_prereqs NUMBER := 0; -- Total number of prerequisites for the course
BEGIN
    -- Get the number of prerequisites for the course related to the section
    SELECT COUNT(*)
    INTO v_num_of_prereqs
    FROM Prerequisite p
    JOIN Section s ON p.mainCourseNumber = s.courseNumber
    WHERE s.sectionId = :NEW.sectionId;

    -- If there are prerequisites, check how many have been taken
    IF v_num_of_prereqs > 0 THEN
        SELECT COUNT(*)
        INTO v_prereqs_taken
        FROM Prerequisite p
        JOIN Section s ON p.mainCourseNumber = s.courseNumber
        WHERE s.sectionId = :NEW.sectionId
          AND EXISTS (
              SELECT 1
              FROM TempTaken t
              WHERE t.courseNumber = p.prerequisiteCourseNumber
                AND t.studentId = :NEW.studentId
                AND t.grade is not NULL
          );

        -- If the number of prerequisites taken does not match the total prerequisites, raise an error
        IF v_prereqs_taken < v_num_of_prereqs THEN
            RAISE_APPLICATION_ERROR(-20004, 'The student has not taken all required prerequisites.');
        END IF;
    END IF;
END;
/



-- Updates probation status if gpa is below 2.0 used for when admin_grades.php assign grades
-- example sql: UPDATE taking set grade = 2 where studentid = 'SD000003' and sectionid = '000000004' 
CREATE OR REPLACE TRIGGER UpdateProbationStatus
AFTER INSERT OR UPDATE OF grade ON Taking
FOR EACH ROW
DECLARE
    v_gpa NUMBER;
BEGIN
    -- Select the GPA for the student involved in the trigger
    SELECT GPA INTO v_gpa
    FROM StudentAcademicInfo
    WHERE studentId = :new.studentId;

    -- Handle probation status based on GPA
    IF v_gpa < 2.0 THEN
        UPDATE Student
        SET status = 'Probation'  -- 'Probation' status, assuming 1 means probation
        WHERE studentId = :new.studentId;
    ELSE
        UPDATE Student
        SET status = 'Good Standing'  -- 'Good Standing' status, assuming 0 means good standing
        WHERE studentId = :new.studentId;
    END IF;
END;
/