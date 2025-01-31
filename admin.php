<?
include "utility_functions.php";

$sessionid =$_GET["sessionid"];
verify_session($sessionid);

// Check if admin:
$checkAdminQuery = 
   "SELECT username 
    FROM Client 
    WHERE username = (
        SELECT username 
        FROM ClientSession 
        WHERE sessionid = '$sessionid'
    )
    AND EXISTS (
        SELECT 1
        FROM Admin 
        WHERE Admin.username = Client.username
    )";
$resultArray = execute_sql_in_oracle($checkAdminQuery);
$result = $resultArray["flag"];
$cursor = $resultArray["cursor"];

if ($result == false) {
    display_oracle_error_message($cursor);
    die("Failed to check admin privileges.");
}

$values = oci_fetch_array($cursor);
$username = $values[0];

if ($username == null) {
    die("Access denied. You must be an admin to access this page.");
}

oci_free_statement($cursor);

echo("What would you like to do? <br /> <br />Modify: <br />");
echo("<UL>
  <LI><A HREF=\"admin_query_admins.php?sessionid=$sessionid\">Admins</A></LI>
  <LI><A HREF=\"admin_query_students.php?sessionid=$sessionid\">Students</A></LI>
  <LI><A HREF=\"admin_query_studentadmins.php?sessionid=$sessionid\">Student Admins</A></LI>
  <LI><A HREF=\"admin_grades.php?sessionid=$sessionid\">Student's grades</A></LI>
  </UL>");

// Go back
echo("<br />");
echo("<br />");
echo"<form method=\"post\" action=\"welcomepage.php?sessionid=$sessionid\">
    <input type=\"submit\" value=\"Go Back\">
    </form>"

?>