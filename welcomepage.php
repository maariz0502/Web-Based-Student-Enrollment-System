<?
include "utility_functions.php";

$sessionid =$_GET["sessionid"];
verify_session($sessionid);


// Here we can generate the content of the welcome page
echo("Student or Admin Menu: <br />");
echo("<UL>
  <LI><A HREF=\"admin.php?sessionid=$sessionid\">Admin</A></LI>
  <LI><A HREF=\"student.php?sessionid=$sessionid\">Student</A></LI>
  </UL>");

echo("<br />");
echo("<br />");
echo("Click <A HREF = \"reset_password.php?sessionid=$sessionid\">here</A> to Reset Password.");
echo("<br />");
echo("Click <A HREF = \"logout_action.php?sessionid=$sessionid\">here</A> to Logout.");
?>