<?php

// Access provided by database configuration file
require_once 'config.php';

//connection to the database
$con = new mysqli($servername, $sqlusername, $sqlpassword, $sqldbname)
	or die("Unable to connect to MySQL");

if (isset($_POST['signup-button']))   {
    $signup_username = $_POST["signup-username"];
	$signup_password = $_POST["signup-password"];
	
	if(empty($signup_username) || empty($signup_password)) {
		echo "Username and password cannot be empty";
	} else {
		$check = mysqli_query($con,"SELECT * FROM table1 WHERE Username = '$signup_username'");
		$check_row = mysqli_fetch_array($check);
		if($check_row["Username"] == $signup_username) {
			echo "Username already exsists, please use different username.";
		} else {
			$insert = mysqli_query($con,"INSERT INTO table1 (Username, Password, Role) VALUES ('$signup_username', '$signup_password','user')");
			$result = mysqli_query($con,"SELECT * FROM table1 WHERE Username = '$signup_username'");
			$row = mysqli_fetch_array($result);
	
			if($row["Username"] == $signup_username) {
				echo "Your account was created successfully.";
			} else {
				echo "Unable to create your account, Please try again.";
			}
		}
	}	
} elseif (!empty($_POST["username"])) {
	$form_username = $_POST["username"];
	$form_password = $_POST["password"];
	
    // Select the database to use
	$result = mysqli_query($con,"SELECT * FROM table1 WHERE Username = '$form_username'");
	$row = mysqli_fetch_array($result);

	if($row["Username"] == $form_username && $row["Password"] == $form_password)
		echo "You are a validated user.";
	else
		echo "Sorry, your credentials are not valid, Please try again.";
} else {
    echo "No data provided.";
}

?>