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

// Modifying / Querying a student
echo("Student Admin Modifying / Querying <br /> <br />");
$clienttype = 'student'; // tells other pages if student/admin/studentadmin

// Generate the query section for admin
echo("
  <form method=\"post\" action=\"admin_query_students.php?sessionid=$sessionid\">
  Student ID: <input type=\"text\" size=\"5\" maxlength=\"8\" name=\"q_studentid\">
  First Name: <input type=\"text\" size=\"5\" maxlength=\"20\" name=\"q_firstname\">
  Last Name: <input type=\"text\" size=\"5\" maxlength=\"20\" name=\"q_lastname\"> </br>
  Student Type: <input type=\"text\" size=\"1\" maxlength=\"1\" name=\"q_studenttype\" pattern=\"[ug]\" title=\"Only 'u' or 'g' is allowed\">
  Probation Status: <input type=\"text\" size=\"15\" maxlength=\"15\" name=\"q_status\">
  Course enrolled in: <input type=\"text\" size=\"5\" maxlength=\"9\" name=\"q_coursenumber\"> </br> 
  <input type=\"submit\" value=\"Search\">
  </form>

  <form method=\"post\" action=\"admin_add.php?sessionid=$sessionid&clienttype=$clienttype\">
  <input type=\"submit\" value=\"Add A New User\">
  </form>
  ");
//   Username: <input type=\"text\" size=\"5\" maxlength=\"20\" name=\"q_username\"> 
//   Password: <input type=\"text\" size=\"5\" maxlength=\"20\" name=\"q_password\"> </br>

// Interpret the query requirements
$q_studentid = $_POST["q_studentid"];
$q_firstname = $_POST["q_firstname"];
$q_lastname = $_POST["q_lastname"];
$q_studenttype = $_POST["q_studenttype"];
$q_status = $_POST["q_status"];
$q_coursenumber = $_POST["q_coursenumber"];


$whereClause = " 1=1 ";

if (isset($q_studentid) and trim($q_studentid)!= "") { 
    $whereClause .= " and student.studentid like '%$q_studentid%'"; 
}
// if (isset($q_username) and trim($q_username)!= "") { 
//     $whereClause .= " and client.username like '%$q_username%'"; 
// }
// if (isset($q_password) and $q_password!= "") { 
//     $whereClause .= " and client.q_password like $q_password"; 
// }
if (isset($q_firstname) and $q_firstname!= "") { 
    $whereClause .= " and client.firstname like '%$q_firstname%'"; 
}
if (isset($q_lastname) and $q_lastname!= "") { 
    $whereClause .= " and client.lastname like '%$q_lastname%'"; 
}
if (isset($q_studenttype) and $q_studenttype!= "") { 
    $whereClause .= " and student.studenttype like '%$q_studenttype%'"; 
}
if (isset($q_status) and $q_status!= "") { 
    $whereClause .= " and student.status like '%$q_status%'"; 
}
if (isset($q_coursenumber) and $q_coursenumber!= "") { 
    $whereClause .= " and section.coursenumber like '%$q_coursenumber%'"; 
}

// Form the query and execute it
$sql = "select student.studentid, client.firstname, client.lastname, student.studenttype, student.status, section.coursenumber
        from client 
        join student on client.username = student.username
        left join Taking on student.studentid = Taking.studentid 
        left join Section on Taking.sectionid = Section.sectionid
        where $whereClause
        and not exists (
        SELECT 1 
        from admin 
        where admin.username = client.username
        )
        order by student.studentid";
//echo($sql);

$result_array = execute_sql_in_oracle ($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if ($result == false){
  display_oracle_error_message($cursor);
  die("Client Query Failed.");
}

// Display the query results
echo "<table border=1>";
echo "<tr> <th>Student ID</th> <th>firstname</th> <th>lastname</th> <th>Student Type</th> <th>Probation Status</th> <th>Course Number</th> <th>Update</th> <th>Delete</th> </tr>";

// Fetch the result from the cursor one by one
while ($values = oci_fetch_array ($cursor)){
  $studentid = $values[0];
  $firstname = $values[1];
  $lastname = $values[2];
  $studenttype = $values[3];
  $status = $values[4];
  $coursenumber = $values[5];
  echo "<tr>
        <td>$studentid</td> <td>$firstname</td> <td>$lastname</td> 
        <td>$studenttype</td> <td>$status</td> <td>$coursenumber</td>
        <td><a href=\"admin_update.php?sessionid=$sessionid&studentid=$studentid&clienttype=$clienttype\">Update</a></td>
        <td><a href=\"admin_delete.php?sessionid=$sessionid&studentid=$studentid&clienttype=$clienttype\">Delete</a></td>
    </tr>";
}
oci_free_statement($cursor);

echo "</table>";
// Go back
echo("<br />");
echo("<br />");
echo"<form method=\"post\" action=\"admin.php?sessionid=$sessionid\">
    <input type=\"submit\" value=\"Go Back\">
    </form>"

?>