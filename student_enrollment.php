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


// Search for sections based on semester and/or partial course number

echo("Student Enrollment Page <br /> <br />");

echo("
  <form method=\"post\" action=\"student_enrollment.php?sessionid=$sessionid\">
  Semester (Season): <input type=\"text\" size=\"5\" maxlength=\"7\" name=\"q_semesterseason\">
  Semester (Year): <input type=\"text\" size=\"5\" maxlength=\"4\" name=\"q_semesteryear\">
  Course Number: <input type=\"text\" size=\"5\" maxlength=\"9\" name=\"q_coursenumber\">
  <input type=\"submit\" value=\"Search\">
  </form>
");

// Interpret the query requirements
$q_semesterseason = $_POST["q_semesterseason"];
$q_semesteryear = $_POST["q_semesteryear"];
$q_coursenumber = $_POST["q_coursenumber"];

// Search based on the values
$whereClause = " 1=1 ";

if (isset($q_semesterseason) and trim($q_semesterseason) != "") { 
    $whereClause .= " AND section.semesterseason LIKE '%$q_semesterseason%'"; 
}

if (isset($q_semesteryear) and trim($q_semesteryear) != "") { 
    $whereClause .= " AND section.semesteryear LIKE '%$q_semesteryear%'"; 
}

if (isset($q_coursenumber) and $q_coursenumber != "") { 
    $whereClause .= " AND section.coursenumber LIKE '%$q_coursenumber%'"; 
}

// Form the query and execute it

$sql = "SELECT section.sectionid, section.coursenumber, course.title, course.credithour, 
        section.semesterseason, section.semesteryear, section.datetime, section.datedays,
        section.deadline, section.capacity, section.currentsize
        FROM section
        JOIN course ON section.coursenumber = course.coursenumber 
        WHERE $whereClause
        ORDER BY course.coursenumber";
        
// Execute the query
$result_array = execute_sql_in_oracle($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if ($result == false) {
  display_oracle_error_message($cursor);
  die("Client Query Failed.");
}

// Display the query results with checkboxes
echo "<form method=\"post\" action=\"student_enrollment_action.php?sessionid=$sessionid\">";
echo "<table border=1>";
echo "<tr> <th>Select</th> <th>Section ID</th> <th>Course Number</th> <th>Course Title</th> <th>Credit Hour(s)</th> 
      <th>Season</th> <th>Year</th> <th>Time</th> <th>Days</th> <th>Deadline</th> <th>Capacity</th> <th>Seats Taken</th> </tr>";

// Fetch the result from the cursor one by one
while ($values = oci_fetch_array($cursor)) {  
    $sectionid = $values[0];
    $coursenumber = $values[1];
    $title = $values[2];
    $credithour = $values[3];
    $season = $values[4];
    $year = $values[5];
    $time = $values[6];
    $days = $values[7];
    $deadline = $values[8];
    $capacity = $values[9];
    $currentsize = $values[10];
    
    echo "<tr>
          <td><input type=\"checkbox\" name=\"selected_sections[]\" value=\"$sectionid\"></td>
          <td>$sectionid</td> <td>$coursenumber</td> <td>$title</td>
          <td>$credithour</td> <td>$season</td> <td>$year</td> <td>$time</td> 
          <td>$days</td> <td>$deadline</td> <td>$capacity</td> <td>$currentsize</td>
        </tr>";
}

oci_free_statement($cursor);

echo "</table>";
echo "<input type=\"submit\" value=\"Enroll in Selected Sections\">";
echo "</form>";

// Go back
echo("<br /><br />");
echo("<form method=\"post\" action=\"student.php?sessionid=$sessionid\">
        <input type=\"submit\" value=\"Go Back\">
      </form>");
?>