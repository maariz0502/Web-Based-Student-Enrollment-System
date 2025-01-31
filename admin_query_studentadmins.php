<?php
include "utility_functions.php";

$sessionid = $_GET["sessionid"];
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

// Modifying / Querying a student admin
echo("Student Admin Modifying / Querying <br /> <br />");
$clienttype = 'studentadmin'; // tells other pages if student/admin/studentadmin

// Generate the query section for admin
echo("
  <form method=\"post\" action=\"admin_query_studentadmins.php?sessionid=$sessionid\">
  Student ID: <input type=\"text\" size=\"5\" maxlength=\"8\" name=\"q_studentid\">
  Admin ID: <input type=\"text\" size=\"5\" maxlength=\"9\" name=\"q_adminid\">
  First Name: <input type=\"text\" size=\"5\" maxlength=\"20\" name=\"q_firstname\">
  Last Name: <input type=\"text\" size=\"5\" maxlength=\"20\" name=\"q_lastname\"> </br>
  Student Type: <input type=\"text\" size=\"1\" maxlength=\"1\" name=\"q_studenttype\" pattern=\"[ug]\" title=\"Only 'u' or 'g' is allowed\">  
  Probation Status: <input type=\"text\" size=\"15\" maxlength=\"15\" name=\"q_status\" >
  Course enrolled in: <input type=\"text\" size=\"5\" maxlength=\"9\" name=\"q_coursenumber\"> 
  Start Date: <input type=\"date\" name=\"q_startdate\">
  <input type=\"submit\" value=\"Search\">
  </form>

  <form method=\"post\" action=\"admin_add.php?sessionid=$sessionid&clienttype=$clienttype\">
  <input type=\"submit\" value=\"Add A New User\">
  </form>
");

// Interpret the query requirements
$q_studentid = $_POST["q_studentid"];
$q_adminid = $_POST["q_adminid"];
$q_firstname = $_POST["q_firstname"];
$q_lastname = $_POST["q_lastname"];
$q_studenttype = $_POST["q_studenttype"];
$q_status = $_POST["q_status"];
$q_coursenumber = $_POST["q_coursenumber"];
$q_startdate = $_POST["q_startdate"];

// Make sure only student admins are shown
$whereClause = " 1=1 AND student.studentid IS NOT NULL AND admin.adminid IS NOT NULL ";

if (isset($q_studentid) and trim($q_studentid) != "") { 
    $whereClause .= " AND student.studentid LIKE '%$q_studentid%'"; 
}

if (isset($q_adminid) and trim($q_adminid) != "") { 
    $whereClause .= " AND admin.adminid LIKE '%$q_adminid%'"; 
}

if (isset($q_firstname) and $q_firstname != "") { 
    $whereClause .= " AND client.firstname LIKE '%$q_firstname%'"; 
}

if (isset($q_lastname) and $q_lastname != "") { 
    $whereClause .= " AND client.lastname LIKE '%$q_lastname%'"; 
}

if (isset($q_studenttype) and $q_studenttype != "") { 
    $whereClause .= " AND student.studenttype LIKE '%$q_studenttype%'"; 
}

if (isset($q_status) and $q_status != "") { 
    $whereClause .= " AND student.status LIKE '%$q_status%'"; 
}

if (isset($q_coursenumber) and $q_coursenumber != "") { 
    $whereClause .= " AND section.coursenumber LIKE '%$q_coursenumber%'"; 
}

if (!empty($q_startdate)) { 
    $whereClause .= " AND admin.startdate LIKE TO_DATE('$q_startdate', 'YYYY-MM-DD')"; 
}

// Form the query and execute it
$sql = "select student.studentid, admin.adminid, client.firstname, client.lastname, 
        student.studenttype, student.status, section.coursenumber, admin.startdate
        from client 
        join student on client.username = student.username
        join admin on client.username = admin.username
        left join Taking on student.studentid = Taking.studentid 
        left join Section on Taking.sectionid = Section.sectionid
        where $whereClause
        order by student.studentid";

// Execute the query
$result_array = execute_sql_in_oracle($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if ($result == false) {
  display_oracle_error_message($cursor);
  die("Client Query Failed.");
}

// Display the query results
echo "<table border=1>";
echo "<tr> <th>Student ID</th> <th>Admin ID</th> <th>First Name</th> <th>Last Name</th> <th>Student Type</th>
      <th>Probation Status</th> <th>Course Number</th> <th>Start Date</th> <th>Update</th> <th>Delete</th> </tr>";

// Fetch the result from the cursor one by one
while ($values = oci_fetch_array($cursor)) {  
    $studentid = $values[0];
    $adminid = $values[1];
    $firstname = $values[2];
    $lastname = $values[3];
    $studenttype = $values[4];
    $status = $values[5];
    $coursenumber = $values[6];
    $startdate = $values[7];

  echo "<tr>
        <td>$studentid</td> <td>$adminid</td> <td>$firstname</td>
        <td>$lastname</td> <td>$studenttype</td> <td>$status</td> 
        <td>$coursenumber</td> <td>$startdate</td>
        <td><a href=\"admin_update.php?sessionid=$sessionid&studentid=$studentid&adminid=$adminid&clienttype=$clienttype\">Update</a></td>
        <td><a href=\"admin_delete.php?sessionid=$sessionid&studentid=$studentid&adminid=$adminid&clienttype=$clienttype\">Delete</a></td>
    </tr>";
}
oci_free_statement($cursor);

echo "</table>";

// Go back
echo("<br />");
echo("<br />");
echo "<form method=\"post\" action=\"admin.php?sessionid=$sessionid\">
    <input type=\"submit\" value=\"Go Back\">
    </form>";
?>
