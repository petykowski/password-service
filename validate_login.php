<?php

// Access provided by database configuration file
require_once 'config.php';

$form_username = $_POST["username"];
$form_password = $_POST["password"];

   //connection to the database
   $con = new mysqli($servername, $sqlusername, $sqlpassword, $sqldbname)
     or die("Unable to connect to MySQL");
     
// Select the database to use
$result = mysqli_query($con,"SELECT * FROM table1 WHERE Username = '$form_username'");

$row = mysqli_fetch_array($result);

if($row["Username"]==$form_username && $row["Password"]== $form_password)
   echo"You are a validated user.";
else
   echo"Sorry, your credentials are not valid, Please try again.";
?>