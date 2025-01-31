<?
include "utility_functions.php";

$sessionid =$_GET["sessionid"];
verify_session($sessionid);
$clienttype = $_GET["clienttype"];  // Get the client type from admin.php or admin_add_action.php
$update_fail = isset($_POST["update_fail"]) ? $_POST["update_fail"] : NULL;

echo $update_fail;

// Obtain the inputs from admin_add_action.php 
if($update_fail == 1){
  if($clienttype == 'admin'){             // Admin
    $adminid = $_POST["adminid"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];
    $startdate = $_POST["startdate"];
  }
  else if($clienttype == 'student'){      // Student
    $username = $_POST["username"];
    $password = $_POST["password"];
    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];
    $age = $_POST["age"];
    $address = $_POST["address"];
    $admissiondate = $_POST["admissiondate"];
    $studenttype = $_POST["studenttype"];
    $standing = $_POST["standing"];
    $concentration = $_POST["concentration"];
  }
  else{                                  // Admin Student
    $adminid = $_POST["adminid"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];
    $age = $_POST["age"];
    $address = $_POST["address"];
    $admissiondate = $_POST["admissiondate"];
    $startdate = $_POST["startdate"];
    $studenttype = $_POST["studenttype"];
    $standing = $_POST["standing"];
    $concentration = $_POST["concentration"];
  }
}


$startdateFormatted = !empty($startdate) ? date('Y-m-d', strtotime($startdate)) : NULL;
$admissiondateFormatted = !empty($admissiondate) ? date('Y-m-d', strtotime($admissiondate)) : NULL;

// display the insertion form.
if($clienttype == 'student'){
  echo("
  <form method=\"post\" action=\"admin_add_action.php?sessionid=$sessionid\">
  Username (Required): <input type=\"text\" value=\"$username\" size=\"20\" maxlength=\"20\" name=\"username\"> 
  Password (Required): <input type=\"text\" value=\"$password\" size=\"12\" maxlength=\"12\" name=\"password\"> </br>
  Student ID (Auto Generated): <input type=\"text\" readonly value=\"$studentid\" size=\"8\" maxlength=\"8\" name=\"studentid\"> </br>
  First Name: <input type=\"text\" value=\"$firstname\" size=\"20\" maxlength=\"20\" name=\"firstname\">
  Last Name: <input type=\"text\" value=\"$lastname\" size=\"20\" maxlength=\"20\" name=\"lastname\"> </br>
  Age: <input type=\"number\" value=\"$age\" size=\"3\" maxlength=\"2\" name=\"age\"> </br>
  Address: <input type=\"text\" value=\"$address\" size=\"40\" maxlength=\"40\" name=\"address\"> </br>
  Admission Date: <input type=\"date\" value=\"$admissiondateFormatted\" name=\"admissiondate\"> </br>
  Student Type (Required): <input type=\"text\" value=\"$studenttype\" size=\"1\" maxlength=\"1\" name=\"studenttype\" pattern=\"[ug]\" title=\"Enter 'u' for undergrad or 'g' for graduate\"> </br>
  Standing: <input type=\"text\" value=\"$standing\" size=\"15\" maxlength=\"15\" name=\"standing\"> 
  Concentration: <input type=\"text\" value=\"$concentration\" size=\"15\" maxlength=\"15\" name=\"concentration\"> </br>
  <input type=\"hidden\" value=\"$clienttype\" name=\"clienttype\">
  
  <input type=\"submit\" value=\"Add\">
  <input type=\"reset\" value=\"Reset to Original Value\">
  </form>
  ");

}
elseif($clienttype == 'admin'){
  echo("
  <form method=\"post\" action=\"admin_add_action.php?sessionid=$sessionid\">
  Username (Required): <input type=\"text\" value=\"$username\" size=\"20\" maxlength=\"20\" name=\"username\"> 
  Password (Required): <input type=\"text\" value=\"$password\" size=\"12\" maxlength=\"12\" name=\"password\"> </br>
  Admin ID (Required): <input type=\"text\" value=\"$adminid\" size=\"9\" maxlength=\"9\" name=\"adminid\"> </br> 
  First Name: <input type=\"text\" value=\"$firstname\" size=\"20\" maxlength=\"20\" name=\"firstname\">  
  Last Name: <input type=\"text\" value=\"$lastname\" size=\"20\" maxlength=\"20\" name=\"lastname\"> </br>
  Start Date: <input type=\"date\" value=\"$startdateFormatted\" name=\"startdate\"> </br>
  <input type=\"hidden\" value=\"$clienttype\" name=\"clienttype\">
  
  <input type=\"submit\" value=\"Add\">
  <input type=\"reset\" value=\"Reset to Original Value\">
  </form>

  ");
}
elseif($clienttype == 'studentadmin'){
  echo("
  <form method=\"post\" action=\"admin_add_action.php?sessionid=$sessionid\">
  Username (Required): <input type=\"text\" value=\"$username\" size=\"20\" maxlength=\"20\" name=\"username\"> 
  Password (Required): <input type=\"text\" value=\"$password\" size=\"12\" maxlength=\"12\" name=\"password\"> </br>
  Student ID (Auto Generated): <input type=\"text\" readonly value=\"$studentid\" size=\"8\" maxlength=\"8\" name=\"studentid\"> 
  Admin ID  (Required): <input type=\"text\" value=\"$adminid\" size=\"9\" maxlength=\"9\" name=\"adminid\"> </br>
  First Name: <input type=\"text\" value=\"$firstname\" size=\"20\" maxlength=\"20\" name=\"firstname\">
  Last Name: <input type=\"text\" value=\"$lastname\" size=\"20\" maxlength=\"20\" name=\"lastname\"> </br>
  Age: <input type=\"number\" value=\"$age\" size=\"3\" maxlength=\"2\" name=\"age\"> </br>
  Address: <input type=\"text\" value=\"$address\" size=\"40\" maxlength=\"40\" name=\"address\"> </br>
  Admission Date: <input type=\"date\" value=\"$admissiondateFormatted\" name=\"admissiondate\"> 
  Start Date: <input type=\"date\" value=\"$startdateFormatted\" name=\"startdate\"> </br>
  Student Type (Required): <input type=\"text\" value=\"$studenttype\" size=\"1\" maxlength=\"1\" name=\"studenttype\" pattern=\"[ug]\" title=\"Enter 'u' for undergrad or 'g' for graduate\"> </br>
  Standing: <input type=\"text\" value=\"$standing\" size=\"15\" maxlength=\"15\" name=\"standing\"> 
  Concentration: <input type=\"text\" value=\"$concentration\" size=\"15\" maxlength=\"15\" name=\"concentration\"> </br>
  <input type=\"hidden\" value=\"$clienttype\" name=\"clienttype\">

  
  <input type=\"submit\" value=\"Add\">
  <input type=\"reset\" value=\"Reset to Original Value\">
  </form>
  ");
}

echo ("
  <form method=\"post\" action=\"admin.php?sessionid=$sessionid\">
  <input type=\"submit\" value=\"Go Back\">
  </form>
  ");

?>

