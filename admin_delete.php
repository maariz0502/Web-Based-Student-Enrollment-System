<?
include "utility_functions.php";

$sessionid =$_GET["sessionid"];
verify_session($sessionid);


// Obtain input from admin.php
$username = $_GET["username"];
$clienttype = $_GET["clienttype"];
if(isset($_GET["studentid"]) && $clienttype == 'student'){                                           // If from admin_query_students
  $studentid = $_GET["studentid"];
}
elseif(isset($_GET["adminid"]) && $clienttype == 'admin'){                                            // If from admin_query_admins
  $adminid = $_GET["adminid"];
}
elseif(isset($_GET["adminid"]) && isset($_GET["studentid"]) && $clienttype == 'studentadmin'){        // If from admin_query_studentadmins
  $adminid = $_GET["adminid"];
  $studentid = $_GET["studentid"];
}

// Different sql statement depending on client type
// Retrieve the tuple to be deleted and display it.
if($clienttype == 'admin'){                      // Admin
  $sql = "select admin.adminid, username, password, 
          firstname, lastname, startdate 
          from client natural join admin
          where adminid = '$adminid'";
}
else if($clienttype == 'student'){             // Student
  $sql = "select student.studentid, username, password, firstname, 
          lastname, student.age, student.address, student.admissiondate, 
          student.studenttype, student.standing, student.concentration, student.status  
          from client natural join student
          where studentid = '$studentid'";
}
else{           // Admin Student
  $sql = "select student.studentid, admin.adminid, 
          client.username, client.password, client.firstname, 
          client.lastname, student.age, student.address,
          student.admissiondate, admin.startdate, student.studenttype, 
          student.standing, student.concentration, student.status  
          from client
          join student on client.username = student.username
          join admin ON client.username = admin.username
          where student.studentid = '$studentid' and admin.adminid = '$adminid'";
}
//echo($sql);

$result_array = execute_sql_in_oracle ($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if ($result == false){ // error unlikely
  display_oracle_error_message($cursor);
  die("Client Query Failed.");
}

if (!($values = oci_fetch_array ($cursor))) {
  // Record already deleted by a separate session.  Go back.
  Header("Location:admin.php?sessionid=$sessionid");
}
oci_free_statement($cursor);

if($clienttype == 'admin'){             // Admin
  $adminid = $values[0];
  $username = $values[1];
  $password = $values[2];
  $firstname = $values[3];
  $lastname = $values[4];
  $startdate = $values[5];
}
else if($clienttype == 'student'){      // Student
  $studentid = $values[0];
  $username = $values[1];
  $password = $values[2];
  $firstname = $values[3];
  $lastname = $values[4];
  $age = $values[5];
  $address = $values[6];
  $admissiondate = $values[7];
  $studenttype = $values[8];
  $standing = $values[9];
  $concentration = $values[10];
  $status = $values[11];
}
else{                                  // Admin Student
  $studentid = $values[0];
  $adminid = $values[1];
  $username = $values[2];
  $password = $values[3];
  $firstname = $values[4];
  $lastname = $values[5];
  $age = $values[6];
  $address = $values[7];
  $admissiondate = $values[8];
  $startdate = $values[9];
  $studenttype = $values[10];
  $standing = $values[11];
  $concentration = $values[12];
  $status = $values[13];
}

// Format the dates
$startdateFormatted = !empty($startdate) ? date('Y-m-d', strtotime($startdate)) : '';
$admissiondateFormatted = !empty($admissiondate) ? date('Y-m-d', strtotime($admissiondate)) : '';

// Display the tuple to be deleted
// Display the record to be updated
if($clienttype == 'student'){
  echo("
  <form method=\"post\" action=\"admin_delete_action.php?sessionid=$sessionid\">
  Username (Read-only): <input type=\"text\" readonly value=\"$username\" size=\"20\" maxlength=\"20\" name=\"username\"> 
  Password (Required): <input type=\"text\" value=\"$password\" size=\"12\" maxlength=\"12\" name=\"password\"> </br>
  Student ID (Read-only): <input type=\"text\" readonly value=\"$studentid\" size=\"8\" maxlength=\"8\" name=\"studentid\"> </br>
  First Name: <input type=\"text\" value=\"$firstname\" size=\"20\" maxlength=\"20\" name=\"firstname\">
  Last Name: <input type=\"text\" value=\"$lastname\" size=\"20\" maxlength=\"20\" name=\"lastname\"> </br>
  Age: <input type=\"number\" value=\"$age\" size=\"3\" maxlength=\"2\" name=\"age\"> </br>
  Address: <input type=\"text\" value=\"$address\" size=\"40\" maxlength=\"40\" name=\"address\"> </br>
  Admission Date: <input type=\"date\" value=\"$admissiondateFormatted\" name=\"admissiondate\"> </br>
  Student Type: <input type=\"text\" value=\"$studenttype\" size=\"1\" maxlength=\"1\" name=\"studenttype\" pattern=\"[ug]\" title=\"Enter 'u' for undergrad or 'g' for graduate\"> </br>
  Standing: <input type=\"text\" value=\"$standing\" size=\"15\" maxlength=\"15\" name=\"standing\"> 
  Concentration: <input type=\"text\" value=\"$concentration\" size=\"15\" maxlength=\"15\" name=\"concentration\"> </br>
  Probation Status: <input type=\"text\" value=\"$status\" size=\"15\" maxlength=\"15\" name=\"status\"> </br>
  <input type=\"hidden\" value=\"$clienttype\" name=\"clienttype\">
  
  <input type=\"submit\" value=\"Delete\">
  <input type=\"reset\" value=\"Reset to Original Value\">
  </form>

  <form method=\"post\" action=\"admin.php?sessionid=$sessionid\">
  <input type=\"submit\" value=\"Go Back\">
  </form>
  ");
}
elseif($clienttype == 'admin'){
  echo("
  <form method=\"post\" action=\"admin_delete_action.php?sessionid=$sessionid\">
  Username (Read-only): <input type=\"text\" readonly value=\"$username\" size=\"20\" maxlength=\"20\" name=\"username\"> 
  Password (Required): <input type=\"text\" value=\"$password\" size=\"12\" maxlength=\"12\" name=\"password\"> </br>
  Admin ID  (Read-only): <input type=\"text\" readonly value=\"$adminid\" size=\"9\" maxlength=\"9\" name=\"adminid\"> </br> 
  First Name: <input type=\"text\" value=\"$firstname\" size=\"20\" maxlength=\"20\" name=\"firstname\">  
  Last Name: <input type=\"text\" value=\"$lastname\" size=\"20\" maxlength=\"20\" name=\"lastname\"> </br>
  Start Date: <input type=\"date\" value=\"$startdateFormatted\" name=\"startdate\"> </br>
  <input type=\"hidden\" value=\"$clienttype\" name=\"clienttype\">
  
  <input type=\"submit\" value=\"Delete\">
  <input type=\"reset\" value=\"Reset to Original Value\">
  </form>

  <form method=\"post\" action=\"admin.php?sessionid=$sessionid\">
  <input type=\"submit\" value=\"Go Back\">
  </form>
  ");
}
elseif($clienttype == 'studentadmin'){
  echo("
  <form method=\"post\" action=\"admin_delete_action.php?sessionid=$sessionid\">
  Username (Read-only): <input type=\"text\" readonly value=\"$username\" size=\"20\" maxlength=\"20\" name=\"username\"> 
  Password (Required): <input type=\"text\" value=\"$password\" size=\"12\" maxlength=\"12\" name=\"password\"> </br>
  Student ID (Read-only): <input type=\"text\" readonly value=\"$studentid\" size=\"8\" maxlength=\"8\" name=\"studentid\"> 
  Admin ID  (Read-only): <input type=\"text\" readonly value=\"$adminid\" size=\"9\" maxlength=\"9\" name=\"adminid\"> </br>
  First Name: <input type=\"text\" value=\"$firstname\" size=\"20\" maxlength=\"20\" name=\"firstname\">
  Last Name: <input type=\"text\" value=\"$lastname\" size=\"20\" maxlength=\"20\" name=\"lastname\"> </br>
  Age: <input type=\"number\" value=\"$age\" size=\"3\" maxlength=\"2\" name=\"age\"> </br>
  Address: <input type=\"text\" value=\"$address\" size=\"40\" maxlength=\"40\" name=\"address\"> </br>
  Admission Date: <input type=\"date\" value=\"$admissiondateFormatted\" name=\"admissiondate\"> 
  Start Date: <input type=\"date\" value=\"$startdateFormatted\" name=\"startdate\"> </br>
  Student Type: <input type=\"text\" value=\"$studenttype\" size=\"1\" maxlength=\"1\" name=\"studenttype\" pattern=\"[ug]\" title=\"Enter 'u' for undergrad or 'g' for graduate\"> </br>
  Standing: <input type=\"text\" value=\"$standing\" size=\"15\" maxlength=\"15\" name=\"standing\"> 
  Concentration: <input type=\"text\" value=\"$concentration\" size=\"15\" maxlength=\"15\" name=\"concentration\"> </br>
  Probation Status: <input type=\"text\" value=\"$status\" size=\"15\" maxlength=\"15\" name=\"status\" </br>
  <input type=\"hidden\" value=\"$clienttype\" name=\"clienttype\">

  
  <input type=\"submit\" value=\"Delete\">
  <input type=\"reset\" value=\"Reset to Original Value\">
  </form>

  <form method=\"post\" action=\"admin.php?sessionid=$sessionid\">
  <input type=\"submit\" value=\"Go Back\">
  </form>
  ");
}
?>