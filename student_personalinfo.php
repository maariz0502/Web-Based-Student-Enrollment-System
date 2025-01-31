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
    
echo("Student's Personal Information<br /> <br />");


// Form the query and execute it
$sql = "select student.studentid, client.firstname, client.lastname, student.studenttype, student.status, student.username
        from client 
        join student on client.username = student.username
        where student.username = 
        (select username from clientsession
        where sessionid='$sessionid')
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
echo "<tr> <th>Student ID</th> <th>firstname</th> <th>lastname</th> <th>Student Type</th> <th>Probation Status</th> <th>Username</th> </tr>";

// Fetch the result from the cursor one by one
while ($values = oci_fetch_array ($cursor)){
  $studentid = $values[0];
  $firstname = $values[1];
  $lastname = $values[2];
  $studenttype = $values[3];
  $status = $values[4];
  $username = $values[5];
  echo "<tr>
        <td>$studentid</td> <td>$firstname</td> <td>$lastname</td> 
        <td>$studenttype</td> <td>$status</td> <td>$username</td>
    </tr>";
}
oci_free_statement($cursor);

echo "</table>";
// Go back
echo("<br />");
echo("<br />");
echo"<form method=\"post\" action=\"student.php?sessionid=$sessionid\">
    <input type=\"submit\" value=\"Go Back\">
    </form>"

?>