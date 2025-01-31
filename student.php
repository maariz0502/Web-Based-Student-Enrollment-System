<?
include "utility_functions.php";

$sessionid =$_GET["sessionid"];
verify_session($sessionid);

// Check if student:
    $checkStudentQuery = 
    "SELECT client.username, student.studentid
        FROM Client
        JOIN Student on client.username=student.username 
        join clientsession on client.username=clientsession.username
        where clientsession.sessionid = '$sessionid'
        ";
    $resultArray = execute_sql_in_oracle($checkStudentQuery);
    $result = $resultArray["flag"];
    $cursor = $resultArray["cursor"];
    
    if ($result == false) {
        display_oracle_error_message($cursor);
        die("Failed to check student privileges.");
    }
    
    $values = oci_fetch_array($cursor);
    $username = $values[0];
    $studentid = $values[1];
    
    if ($username == null) {
        die("Access denied. You must be an student to access this page.");
    }
    
    oci_free_statement($cursor);
     
 echo("Where would you like to go? <br />");
 echo("<UL>
   <LI><A HREF=\"student_personalinfo.php?sessionid=$sessionid\">Student Personal Information</A></LI>
   <LI><A HREF=\"student_academicinfo.php?sessionid=$sessionid\">Student Academic Information</A></LI>
   <LI><A HREF=\"student_enrollment.php?sessionid=$sessionid\">Student Enrollment</A></LI>
   </UL>"); 

echo("
<form method=\"post\" action=\"welcomepage.php?sessionid=$sessionid\">
<input type=\"submit\" value=\"Go Back\">
</form>"
);
?>