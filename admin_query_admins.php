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

// Modifying / Querying an admin
echo("Admin Modifying / Querying <br /> <br />");
$clienttype = 'admin'; // tells other pages if student/admin/studentadmin

// Generate the query section for admin
echo("
  <form method=\"post\" action=\"admin_query_admins.php?sessionid=$sessionid\">
  Admin ID: <input type=\"text\" size=\"5\" maxlength=\"9\" name=\"q_adminid\">
  Username: <input type=\"text\" size=\"5\" maxlength=\"20\" name=\"q_username\"> 
  Password: <input type=\"text\" size=\"5\" maxlength=\"20\" name=\"q_password\"> </br>
  First Name: <input type=\"text\" size=\"5\" maxlength=\"20\" name=\"q_firstname\">
  Last Name: <input type=\"text\" size=\"5\" maxlength=\"20\" name=\"q_lastname\"> </br>
  Start Date: <input type=\"date\" name=\"q_startdate\">
  <input type=\"submit\" value=\"Search\">
  </form>

  <form method=\"post\" action=\"admin_add.php?sessionid=$sessionid&clienttype=$clienttype\">
  <input type=\"submit\" value=\"Add A New User\">
  </form>
  ");


// Interpret the query requirements
$q_adminid = $_POST["q_adminid"];
$q_username = $_POST["q_username"];
$q_password = $_POST["q_password"];
$q_firstname = $_POST["q_firstname"];
$q_lastname = $_POST["q_lastname"];
$q_startdate = $_POST["q_startdate"];

$whereClause = " 1=1 ";

if (isset($q_adminid) and trim($q_adminid)!= "") { 
    $whereClause .= " and admin.adminid like '%$q_adminid%'"; 
}

if (isset($q_username) and trim($q_username)!= "") { 
    $whereClause .= " and client.username like '%$q_username%'"; 
}

if (isset($q_password) and $q_password!= "") { 
    $whereClause .= " and client.q_password like $q_password"; 
}

if (isset($q_firstname) and $q_firstname!= "") { 
    $whereClause .= " and client.firstname like '%$q_firstname%'"; 
}

if (isset($q_lastname) and $q_lastname!= "") { 
    $whereClause .= " and client.lastname like '%$q_lastname%'"; 
}
if (!empty($q_startdate)) { 
    $whereClause .= " and admin.startdate like TO_DATE('$q_startdate', 'YYYY-MM-DD')"; 
}

// Form the query and execute it
$sql = "select admin.adminid, client.username, client.password, client.firstname, 
        client.lastname, admin.startdate 
        from client 
        join admin on client.username = admin.username 
        where $whereClause 
        and not exists (
        SELECT 1 
        from student 
        where student.username = client.username
        )
        order by client.username";
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
echo "<tr> <th>Admin ID</th> <th>Username</th> <th>Password</th> <th>firstname</th> <th>lastname</th> <th>Start Date</th> <th>Update</th> <th>Delete</th> </tr>";

// Fetch the result from the cursor one by one
while ($values = oci_fetch_array ($cursor)){
  $adminid = $values[0];
  $username = $values[1];
  $password = $values[2];
  $firstname = $values[3];
  $lastname = $values[4];
  $startdate = $values[5];
  echo "<tr>
        <td>$adminid</td> <td>$username</td> <td>$password</td> 
        <td>$firstname</td> <td>$lastname</td>
        <td>$startdate</td>
        <td><a href=\"admin_update.php?sessionid=$sessionid&adminid=$adminid&clienttype=$clienttype\">Update</a></td>
        <td><a href=\"admin_delete.php?sessionid=$sessionid&adminid=$adminid&clienttype=$clienttype\">Delete</a></td>
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