<?

// Display sections students are in
// Ask admin to type a section and studentid and grade
// Update the students grade in that section with the entered grades
// Trigger will 

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

echo("Which student's grade would you like to change? <br /><br />");

// Generate the query section for admin
echo("
  <form method=\"post\" action=\"admin_grades.php?sessionid=$sessionid\">
  Student ID: <input type=\"text\" size=\"8\" maxlength=\"8\" name=\"u_studentid\" required>
  Section ID: <input type=\"text\" size=\"9\" maxlength=\"9\" name=\"u_sectionid\" required> 
  New Final Grade (1.0 - 4.0): <input type=\"text\" size=\"1\" maxlength=\"1\" name=\"u_grade\" required> <br />
  <input type=\"submit\" value=\"Update\">
  </form>
");

// Interpret the submitted values
$u_studentid = isset($_POST["u_studentid"]) ? $_POST["u_studentid"] : NULL;
$u_sectionid = isset($_POST["u_sectionid"]) ? $_POST["u_sectionid"] : NULL;
$u_grade = isset($_POST["u_grade"]) ? $_POST["u_grade"] : NULL;

if($u_studentid != NULL && $u_sectionid != NULL && $u_grade != NULL){
    // Form the query and execute it
    $sql = "UPDATE taking 
            set grade = $u_grade
            where studentid = '$u_studentid'
            and sectionid = '$u_sectionid'
            ";
    echo($sql);
    // Execute the query
    $result_array = execute_sql_in_oracle($sql);
    $result = $result_array["flag"];
    $cursor = $result_array["cursor"];

    if ($result == false) {
        display_oracle_error_message($cursor);
        die("<i>
        <form method=\"post\" action=\"admin_grades.php?sessionid=$sessionid\">
    
        Read the error message, and then try again:
        <input type=\"submit\" value=\"Go Back\">
        </form>
        </i>"
        );  
    }
}

echo("<br />Students With Courses <br /> <br />");

// Show students who are currently taking a course
// Form the sql from studentacademicinfo 
$sql = "SELECT t.studentid, t.sectionid, 
        sec.coursenumber, co.title, 
        co.credithour, t.grade
        FROM Taking t
        JOIN section sec on t.sectionid = sec.sectionid
        JOIN Course co ON sec.courseNumber = co.courseNumber
        ORDER BY t.studentId
        --where t.studentid is not null
        ";
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
echo "<tr> <th>Student ID</th> <th>Section ID</th> <th>Course Number</th> <th>Course Title</th> <th>Credits</th> <th>Grade</th> </tr>";

// Fetch the result from the cursor one by one
while ($values = oci_fetch_array ($cursor)){
  $studentid = $values[0];
  $sectionid = $values[1];
  $coursenumber = $values[2];
  $coursetitle = $values[3];
  $credits = $values[4];
  $grade = $values[5];
  echo "<tr>
        <td>$studentid</td> <td>$sectionid</td> <td>$coursenumber</td>
        <td>$coursetitle</td> <td>$credits</td> <td>$grade</td> 
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