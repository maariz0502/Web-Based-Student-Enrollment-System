<?
ini_set( "display_errors", 0);  

include "utility_functions.php";

$sessionid =$_GET["sessionid"];
verify_session($sessionid);
$clienttype =$_POST["clienttype"];

if($clienttype == 'admin'){
  $adminid = $_POST["adminid"];
  $username = $_POST["username"];
  $password = $_POST["password"];
  $firstname = isset($_POST["firstname"]) ? $_POST["firstname"] : NULL;
  $lastname = isset($_POST["lastname"]) ? $_POST["lastname"] : NULL;
  $startdate = isset($_POST["startdate"]) ? $_POST["startdate"] : NULL;
  
  // the sql string
  $sql = "
    BEGIN
        -- Insert into Client table
        insert into Client values (
        '$username',
        '$password',
        '$firstname',
        '$lastname'
        );

        -- Insert into Admin table
        insert into Admin values (
        '$adminid',
        TO_DATE('$startdate', 'YYYY-MM-DD'),
        '$username'
        );
    END;";
}
elseif($clienttype == 'student'){ 
  $username = $_POST["username"];
  $password = $_POST["password"];
  $firstname = isset($_POST["firstname"]) ? $_POST["firstname"] : NULL;
  $lastname = isset($_POST["lastname"]) ? $_POST["lastname"] : NULL;
  $studenttype = isset($_POST["studenttype"]) ? $_POST["studenttype"] : NULL;
  $age = isset($_POST["age"]) ? $_POST["age"] : NULL;
  $address = isset($_POST["address"]) ? $_POST["address"] : NULL;
  $admissiondate = isset($_POST["admissiondate"]) ? $_POST["admissiondate"] : NULL;
  $standing = isset($_POST["standing"]) ? $_POST["standing"] : NULL;
  $concentration = isset($_POST["concentration"]) ? $_POST["concentration"] : NULL;
  
  // Prepare the SQL block to call the procedure 
  $sql = "
  BEGIN
    add_student_using_counter(
        p_username => '$username',
        p_password => '$password',
        p_firstName => '$firstname',
        p_lastName => '$lastname',
        p_age => $age,
        p_address => '$address',
        p_admissiondate => TO_DATE('$admissiondate', 'YYYY-MM-DD'),
        p_studentType => '$studenttype',
        p_standing => '$standing',
        p_concentration => '$concentration'
    );
  END;
  ";
}
elseif($clienttype == 'studentadmin'){
  $adminid = $_POST["adminid"];
  $username = $_POST["username"];
  $password = $_POST["password"];
  $firstname = isset($_POST["firstname"]) ? $_POST["firstname"] : NULL;
  $lastname = isset($_POST["lastname"]) ? $_POST["lastname"] : NULL;
  $startdate = isset($_POST["startdate"]) ? $_POST["startdate"] : NULL;
  $studenttype = isset($_POST["studenttype"]) ? $_POST["studenttype"] : NULL;
  $age = isset($_POST["age"]) ? $_POST["age"] : NULL;
  $address = isset($_POST["address"]) ? $_POST["address"] : NULL;
  $admissiondate = isset($_POST["admissiondate"]) ? $_POST["admissiondate"] : NULL;
  $standing = isset($_POST["standing"]) ? $_POST["standing"] : NULL;
  $concentration = isset($_POST["concentration"]) ? $_POST["concentration"] : NULL;
  
  // the sql string
  $sql = "
  BEGIN
      add_student_using_counter(
        p_username => '$username',
        p_password => '$password',
        p_firstName => '$firstname',
        p_lastName => '$lastname',
        p_age => $age,
        p_address => '$address',
        p_admissiondate => TO_DATE('$admissiondate', 'YYYY-MM-DD'),
        p_studentType => '$studenttype',
        p_standing => '$standing',
        p_concentration => '$concentration'
      );

      -- Update Admin table
      insert into Admin values (
      '$adminid',
      TO_DATE('$startdate', 'YYYY-MM-DD'),
      '$username'
      );
  END;";

}

echo($sql);

$result_array = execute_sql_in_oracle ($sql);
$result = $result_array["flag"];
$cursor = $result_array["cursor"];

if ($result == false){
  echo "<B>Insertion Failed.</B> <BR />";

  display_oracle_error_message($cursor);
  
  die("<i> 
  <form method=\"post\" action=\"admin_add.php?sessionid=$sessionid\">

  <input type=\"hidden\" value = \"$adminid\" name=\"adminid\">
  <input type=\"hidden\" value = \"$username\" name=\"username\">
  <input type=\"hidden\" value = \"$passworde\" name=\"passworde\">
  <input type=\"hidden\" value = \"$firstname\" name=\"firstname\">
  <input type=\"hidden\" value = \"$lastname\" name=\"lastname\">
  <input type=\"hidden\" value = \"$startdate\" name=\"startdate\">
  <input type=\"hidden\" value = \"$studenttype\" name=\"studenttype\">
  <input type=\"hidden\" value = \"$age\" name=\"age\">
  <input type=\"hidden\" value = \"$address\" name=\"address\">
  <input type=\"hidden\" value = \"$admissiondate\" name=\"admissiondate\">
  <input type=\"hidden\" value = \"$standing\" name=\"standing\">
  <input type=\"hidden\" value = \"$concentration\" name=\"concentration\">
  <input type=\"hidden\" value = \"1\" name=\"update_fail\">
  
  Read the error message, and then try again:
  <input type=\"submit\" value=\"Go Back\">
  </form>

  </i>
  ");
}

Header("Location:admin.php?sessionid=$sessionid");
?>