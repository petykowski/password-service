<?php

// Access provided by database configuration file
require_once 'config.php';

//connection to the database
$con = new mysqli($servername, $sqlusername, $sqlpassword, $dbname)

if (mysqli_connect_errno($con)) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
$username = $_GET['username'];
$password = $_GET['password'];
$result = mysqli_query($con,"SELECT Role FROM table1 where Username='$username' and Password='$password'");
$row = mysqli_fetch_array($result);
$data = $row[0];
if($data){
	echo $data;
}
mysqli_close($con);
?>