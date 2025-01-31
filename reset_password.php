<?
include "utility_functions.php";

$sessionid =$_GET["sessionid"];
verify_session($sessionid);

echo("Password reset page <br />");

// Button to go back to welcomepage.php
echo ( 
  "<form method=\"post\" action=\"welcomepage.php?sessionid=$sessionid\">
<input type=\"submit\" value=\"Go Back\">
</form>" .
// Field for user to input password
"<form method=\"post\" action=\"reset_password.php?sessionid=$sessionid\">
New Password: <input type=\"text\" size=\"5\" maxlength=\"12\" name=\"q_password\"> 
<input type=\"submit\" value=\"Reset\">
</form>"
);

  $q_password = $_POST["q_password"];
  $sql = "UPDATE client " .
          "SET password='$q_password' " .
          "WHERE username = " .
          "(SELECT username FROM clientsession " .                    
          "WHERE sessionid='$sessionid')";

  if ($q_password != "") {

    // Update password in the database
  $sql = "UPDATE client " .
          "SET password = '$q_password' " .
          "WHERE client.username = " .
          "(SELECT clientsession.username FROM clientsession " .                    
          "WHERE clientsession.sessionid = '$sessionid')";

  $result_array = execute_sql_in_oracle ($sql);
  $result = $result_array["flag"];
  $cursor = $result_array["cursor"];
      
  if ($result == false) {
      display_oracle_error_message($cursor);
      die("Password reset failed");
  } else {
      echo "Password has been reset successfully.";
      // Redirect to login page after successful password reset
      //header("Location: login.html");
  }
}
?>