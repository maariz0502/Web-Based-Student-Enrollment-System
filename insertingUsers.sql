-- 2 Admin
insert into Client values('admin1', 'admin1', 'Ad', 'Min1');
insert into Admin values('AM000001', to_date('02052022', 'mmddyyyy'), 'admin1');

insert into Client values('admin2', 'admin2', 'Ad', 'Min2');                  
insert into Admin values('AM000002', to_date('08152023', 'mmddyyyy'), 'admin2');


-- 2 Student Admin
insert into Client values('studentadmin1', 'studentadmin1', 'Stu', 'Adm1');         
insert into Student values('SA000001', 19, '4324 9th Street', to_date('08162022', 'mmddyyyy'), 'u', 'Sophomore', NULL, 'Not Established', 'studentadmin1');         
insert into Admin values('SA000001', to_date('02052022', 'mmddyyyy'), 'studentadmin1');

insert into Client values('studentadmin2', 'studentadmin2', 'Stu', 'Adm2');         
insert into Student values('SA000002', 18, '2489 Bread Street', to_date('08162022', 'mmddyyyy'), 'g', NULL, NULL, 'Not Established', 'studentadmin2');         
insert into Admin values('SA000002', to_date('02052022', 'mmddyyyy'), 'studentadmin2');


-- 6 Students
insert into Client values('student1', 'student1', 'Stu', 'Dent1');         
insert into Student values('SD000003', 18, '1234 Robbin Lane Avenue', to_date('08162022', 'mmddyyyy'), 'u', 'Sophomore', NULL, 'Not Established', 'student1');         

insert into Client values('student2', 'student2', 'Stu', 'Dent2');         
insert into Student values('SD000004', 19, '3245 MLK Avenue', to_date('08162021', 'mmddyyyy'), 'u', 'Junior', NULL, 'Not Established', 'student2');         

insert into Client values('student3', 'student3', 'Stu', 'Dent3');         
insert into Student values('SD000005', 22, '3123 NW 2nd Street', to_date('08162020', 'mmddyyyy'), 'u', 'Senior', NULL, 'Not Established', 'student3');         

insert into Client values('student4', 'student4', 'Stu', 'Dent4');         
insert into Student values('SD000006', 20, '3252 NW 36th Street', to_date('08162023', 'mmddyyyy'), 'u', 'Freshman', NULL, 'Not Established', 'student4');         

insert into Client values('student5', 'student5', 'Stu', 'Dent5');         
insert into Student values('SD000007', 23, '9832 Bin Drive', to_date('08162024', 'mmddyyyy'), 'g', NULL, 'AI', 'Not Established', 'student5');         

insert into Client values('student6', 'student6', 'Stu', 'Dent6');         
insert into Student values('SD000008', 24, '1001 Colossal Avenue', to_date('05162023', 'mmddyyyy'), 'g', NULL, 'Cybersecurity', 'Not Established', 'student6');     


-- Update counter table
insert into counter_table values (8);


-- 8 Courses
insert into course values('CMSC3613', 'Data Structures', 3);
insert into course values('CMSC1613', 'Programming I', 3);
insert into course values('CMSC1621', 'Programming I Labaratory', 1);
insert into course values('PHYS1114', 'Gen Physics I And Lab', 4);
insert into course values('PHIL1103', 'Logic And Critical Thinking', 3);
insert into course values('ENGL1010', 'English Composition', 3);
insert into course values('MATH2313', 'Calculus 1', 3);
insert into course values('CMSC4003', 'Applications of Database Management Systems', 3);


-- 10 Sections
insert into section values('000000001', to_date('08102024', 'mmddyyyy'), 10, 2024, 'Fall', '08:00 AM', 'TR', 'CMSC3613', 0);
insert into section values('000000002', to_date('01102025', 'mmddyyyy'), 5, 2025, 'Spring', '11:50 AM', 'TR', 'PHYS1114', 0);
insert into section values('000000003', to_date('01102025', 'mmddyyyy'), 10, 2025, 'Spring', '03:00 PM', 'TR', 'PHIL1103', 0);
insert into section values('000000004', to_date('01102025', 'mmddyyyy'), 5, 2025, 'Spring', '06:30 PM', 'TR', 'CMSC3613', 0);
insert into section values('000000005', to_date('01102025', 'mmddyyyy'), 5, 2025, 'Spring', '08:00 AM', 'MWF', 'MATH2313', 0);
insert into section values('000000006', to_date('01102025', 'mmddyyyy'), 3, 2025, 'Spring', '11:50 AM', 'MWF', 'CMSC4003', 0);
insert into section values('000000007', to_date('01102025', 'mmddyyyy'), 7, 2025, 'Spring', '4:00 PM', 'MWF', 'CMSC1613', 0);
insert into section values('000000008', to_date('01102025', 'mmddyyyy'), 6, 2025, 'Spring', '12:00 PM', 'MW', 'CMSC1621', 0);
insert into section values('000000009', to_date('01102025', 'mmddyyyy'), 10, 2025, 'Spring', '02:30 PM', 'MW', 'PHIL1103', 0);
insert into section values('000000010', to_date('01102025', 'mmddyyyy'), 2, 2025, 'Spring', '07:30 PM', 'MW', 'ENGL1010', 0);


-- CMSC1613 AND CMSC1621 is prerequisite to CMSC3613 prereq to CMSC4003
insert into Prerequisite values('CMSC1613', 'CMSC3613');
insert into Prerequisite values('CMSC1621', 'CMSC3613');
insert into Prerequisite values('CMSC3613', 'CMSC4003');

-- Enroll students in courses
insert into Taking values('000000008', 'SD000003', 3);
insert into Taking values('000000007', 'SD000003', 3);
insert into Taking values('000000004', 'SD000003', NULL);
insert into Taking values('000000010', 'SD000003', 3);
insert into Taking values('000000008', 'SD000004', 3);
insert into Taking values('000000007', 'SD000004', 3);
insert into Taking values('000000004', 'SD000004', 3);



commit;