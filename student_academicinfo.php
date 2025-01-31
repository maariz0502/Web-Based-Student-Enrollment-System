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
  
 echo("Student's Academic Information<br /> <br />");
 
// •	Student Academic Information page: A student user can display his/her academic information, 
//      including the number of courses completed, total credit hours earned, and GPA.  GPA can be calculated as follows: 
//      SUM of all (course grade * course credit) / SUM of all credits

// Form the query and execute it
$sql = "select studentid, username, courses_completed, 
        total_credits, GPA
        from studentacademicinfo 
        where username = '$username'
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
echo "<tr> <th>Student ID</th> <th>Username</th> <th>Courses Complete</th> <th>Total Credits</th> <th>GPA</th> </tr>";

// Fetch the result from the cursor one by one
while ($values = oci_fetch_array ($cursor)){
  $studentid = $values[0];
  $username = $values[1];
  $coursescompleted = $values[2];
  $totalcredits = $values[3];
  $GPA = $values[4];
  echo "<tr>
        <td>$studentid</td> <td>$username</td> <td>$coursescompleted</td> 
        <td>$totalcredits</td> <td>$GPA</td> 
    </tr>";
}
oci_free_statement($cursor);

//   •	On the same page of a student user’s academic information, the system should also display 
//      all the sections that the student has taken and is currently taking. For each section, 
//      display section id, course number, course title, semester (season and year), number of credits, and grade (if completed).


// Form the query and execute it
$sql = "select taking.sectionid, section.coursenumber, course.title, section.semesterseason,
        section.semesteryear, course.credithour, taking.grade
        from student 
        join taking on student.studentid=taking.studentid
        join section on taking.sectionid=section.sectionid
        join course on section.coursenumber=course.coursenumber
        where username = '$username'
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
echo "<tr> <th>Section ID</th> <th>Course Number</th> <th>Courses Title</th> <th>Semester Season</th> <th>Semester Year</th> <th>Credit Hour(s)</th> <th>Grade</th> </tr>";

// Fetch the result from the cursor one by one
while ($values = oci_fetch_array ($cursor)){
  $sectionid = $values[0];
  $coursenumber = $values[1];
  $coursetitle = $values[2];
  $semesterseason = $values[3];
  $semesteryear = $values[4];
  $credithour = $values[5];
  $grade = $values[6];
  echo "<tr>
        <td>$sectionid</td> <td>$coursenumber</td> <td>$coursetitle</td> 
        <td>$semesterseason</td> <td>$semesteryear</td> <td>$credithour</td> 
        <td>$grade</td> 
    </tr>";
}
oci_free_statement($cursor);

echo("<br /> Courses Completed or Taking <br />");

echo "</table>";
// Go back
echo("<br />");
echo("<br />");
echo"<form method=\"post\" action=\"student.php?sessionid=$sessionid\">
    <input type=\"submit\" value=\"Go Back\">
    </form>"
?>