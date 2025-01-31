<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
verify_session($sessionid);

// Suppress PHP auto warning.
ini_set("display_errors", 0);  

// Get input from admin_update.php and update the record.
$clienttype = $_POST["clienttype"];  // This field determines if the user is a student, student admin, or admin
if($clienttype == 'admin' || $clienttype == 'student' || $clienttype == 'studentadmin'){
  $username = $_POST["username"];
  $password = $_POST["password"];
  $firstname = $_POST["firstname"];
  $lastname = $_POST["lastname"];
}
if($clienttype == 'admin' || $clienttype == 'studentadmin'){
  $adminid = $_POST["adminid"];
  $startdate = $_POST["startdate"];
}
if($clienttype == 'student' || $clienttype == 'studentadmin'){
  $studentid = $_POST["studentid"];
  $age = $_POST["age"];
  $address = $_POST["address"];
  $admissiondate = $_POST["admissiondate"];
  $studenttype = $_POST["studenttype"];
  $standing = $_POST["standing"];
  $concentration = $_POST["concentration"];
  $status = $_POST["status"];
}

// PL/SQL string based on client type
if ($clienttype == 'student') {
    // PL/SQL block for student
    $plsql = "
    BEGIN
        -- Update Client table
        UPDATE Client 
        SET password = '$password', 
            firstName = '$firstname', 
            lastName = '$lastname'
        WHERE username = '$username';

        -- Update Student table
        UPDATE Student
        SET age = '$age', 
            address = '$address', 
            admissiondate = TO_DATE('$admissiondate', 'YYYY-MM-DD'),
            studentType = '$studenttype', 
            standing = '$standing',
            concentration = '$concentration',
            status = '$status'
        WHERE username = '$username';
    END;";
} 
elseif ($clienttype == 'studentadmin') {
    // PL/SQL block for studentadmin
    $plsql = "
    BEGIN
        -- Update Client table
        UPDATE Client 
        SET password = '$password', 
            firstName = '$firstname', 
            lastName = '$lastname'
        WHERE username = '$username';

        -- Update Student table
        UPDATE Student
        SET age = '$age', 
            address = '$address', 
            admissiondate = TO_DATE('$admissiondate', 'YYYY-MM-DD'),
            studentType = '$studenttype', 
            standing = '$standing',
            concentration = '$concentration',
            status = '$status'
        WHERE username = '$username';

        -- Update Admin table
        UPDATE Admin
        SET startDate = TO_DATE('$startdate', 'YYYY-MM-DD')
        WHERE username = '$username';
    END;";
} 
elseif ($clienttype == 'admin') {
    // PL/SQL block for admin
    $plsql = "
    BEGIN
        -- Update Client table
        UPDATE Client 
        SET password = '$password', 
            firstName = '$firstname', 
            lastName = '$lastname'
        WHERE username = '$username';

        -- Update Admin table
        UPDATE Admin
        SET startDate = TO_DATE('$startdate', 'YYYY-MM-DD')
        WHERE username = '$username';
    END;";
}

// Execute the PL/SQL block
echo($plsql);  

$result_array = execute_sql_in_oracle($plsql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];


if ($result == false) {
  // Error handling interface
  echo "<B>Update Failed.</B> <BR />";
  display_oracle_error_message($cursor);

// Change fields based on clienttype
  if ($clienttype == 'student') {
        die("<i>
        <form method=\"post\" action=\"admin_update.php?sessionid=$sessionid\">
        <input type=\"hidden\" value=\"$studentid\" name=\"studentid\">
        <input type=\"hidden\" value=\"$username\" name=\"username\">
        <input type=\"hidden\" value=\"$firstname\" name=\"firstname\">
        <input type=\"hidden\" value=\"$lastname\" name=\"lastname\">
        <input type=\"hidden\" value=\"$age\" name=\"age\">
        <input type=\"hidden\" value=\"$address\" name=\"address\">
        <input type=\"hidden\" value=\"$admissiondate\" name=\"admissiondate\">
        <input type=\"hidden\" value=\"$studenttype\" name=\"studenttype\">
        <input type=\"hidden\" value=\"$standing\" name=\"standing\">
        <input type=\"hidden\" value=\"$concentration\" name=\"concentration\">
        <input type=\"hidden\" value=\"$status\" name=\"status\">
        <input type=\"hidden\" value=\"$clienttype\" name=\"clienttype\">
        <input type=\"hidden\" value=\"1\" name=\"update_fail\">
        Read the error message, and then try again:
        <input type=\"submit\" value=\"Go Back\">
      </form>
      </i>");
  }
  elseif ($clienttype == 'studentadmin') {
        die("<i>
        <form method=\"post\" action=\"admin_update.php?sessionid=$sessionid\">
        <input type=\"hidden\" value=\"$studentid\" name=\"studentid\">
        <input type=\"hidden\" value=\"$adminid\" name=\"adminid\">
        <input type=\"hidden\" value=\"$username\" name=\"username\">
        <input type=\"hidden\" value=\"$firstname\" name=\"firstname\">
        <input type=\"hidden\" value=\"$lastname\" name=\"lastname\">
        <input type=\"hidden\" value=\"$age\" name=\"age\">
        <input type=\"hidden\" value=\"$address\" name=\"address\">
        <input type=\"hidden\" value=\"$admissiondate\" name=\"admissiondate\">
        <input type=\"hidden\" value=\"$startdate\" name=\"startdate\">
        <input type=\"hidden\" value=\"$studenttype\" name=\"studenttype\">
        <input type=\"hidden\" value=\"$standing\" name=\"standing\">
        <input type=\"hidden\" value=\"$concentration\" name=\"concentration\">
        <input type=\"hidden\" value=\"$status\" name=\"status\">
        <input type=\"hidden\" value=\"$clienttype\" name=\"clienttype\">
        <input type=\"hidden\" value=\"1\" name=\"update_fail\">
        Read the error message, and then try again:
        <input type=\"submit\" value=\"Go Back\">
      </form>
      </i>");  
  }
  elseif ($clienttype == 'admin') {
        die("<i>
        <form method=\"post\" action=\"admin_update.php?sessionid=$sessionid\">
        <input type=\"hidden\" value=\"$adminid\" name=\"adminid\">
        <input type=\"hidden\" value=\"$username\" name=\"username\">
        <input type=\"hidden\" value=\"$firstname\" name=\"firstname\">
        <input type=\"hidden\" value=\"$lastname\" name=\"lastname\">
        <input type=\"hidden\" value=\"$startdate\" name=\"startdate\">
        <input type=\"hidden\" value=\"$clienttype\" name=\"clienttype\">
        <input type=\"hidden\" value=\"1\" name=\"update_fail\">
        Read the error message, and then try again:
        <input type=\"submit\" value=\"Go Back\">
      </form>
      </i>");  
  }
}

// Record updated successfully. Redirect back to admin page.
Header("Location: admin.php?sessionid=$sessionid");
?>
