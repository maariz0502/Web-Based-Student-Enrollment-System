<?php
include "utility_functions.php";

// Verify session and check if user is a student
$sessionid = $_GET["sessionid"];
verify_session($sessionid);


// Check if student:
// Additionally retrive studentid
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

// Retrieve selected section IDs
if (!isset($_POST['selected_sections']) || empty($_POST['selected_sections'])) {
  die("<i>
  <form method=\"post\" action=\"student_enrollment.php?sessionid=$sessionid\">

  No sections selected for enrollment. Please go back and select at least one section: 
  <input type=\"submit\" value=\"Go Back\">
  </form>
  </i>");
}
$selected_sections = $_POST['selected_sections'];

// Enroll the student in the selected sections
foreach ($selected_sections as $sectionid) {
  $enrollQuery = 
  "INSERT INTO Taking (studentId, sectionId) VALUES ('$studentid', '$sectionid')";

  $resultArray = execute_sql_in_oracle($enrollQuery);
  $result = $resultArray["flag"];

  if ($result == false) {
    echo "<B>Enrollment Failed.</B> <BR />";
    echo "Failed to enroll in section ID: $sectionid for the reason below:";
    display_oracle_error_message($resultArray["cursor"]);
    die("<i>
    <form method=\"post\" action=\"student_enrollment.php?sessionid=$sessionid\">
  
    Read the error message, and then try again:
    <input type=\"submit\" value=\"Go Back\">
    </form>
    </i>");
  }
}


// Enrolled into class successfully. Redirect back to student_enrollment page.
echo("<form method=\"post\" action=\"student_enrollment.php?sessionid=$sessionid\">
Enrolled successfully! <BR />
<input type=\"submit\" value=\"Go Back\">
</form>
</i>");
?>