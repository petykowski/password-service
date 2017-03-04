<?php

// Prepare for JSON Response
header('Content-Type: application/json');

// Access provided by database configuration file
require_once 'config.php';

//Connect to Database
$con = new mysqli($servername, $sqlusername, $sqlpassword, $sqldbname)
	or die("Unable to connect to MySQL");

switch ($_POST["action"]) {
	case "validate_username":
		$signup_username = $_POST["signup-username"];
		$check = mysqli_query($con,"SELECT * FROM table1 WHERE Username = '$signup_username'");
		$check_row = mysqli_fetch_array($check);
		if($check_row["Username"] == $signup_username) {
			$arr = array('response' => 'Failure', 'message' => 'Username already exsists, please use different username.');
			echo json_encode($arr);
		}
		break;
	case "signup":
		$signup_username = $_POST["signup-username"];
		$signup_password = $_POST["signup-password"];

		if(empty($signup_username) || empty($signup_password)) {
			echo "Username and password cannot be empty";
		} else {
			$check = mysqli_query($con,"SELECT * FROM table1 WHERE Username = '$signup_username'");
			$check_row = mysqli_fetch_array($check);
			if($check_row["Username"] == $signup_username) {
				$arr = array('response' => 'Failure', 'message' => 'Username already exsists, please use different username.');
				echo json_encode($arr);
			} else {
				$insert = mysqli_query($con,"INSERT INTO table1 (Username, Password, Role) VALUES ('$signup_username', '$signup_password','user')");
				$result = mysqli_query($con,"SELECT * FROM table1 WHERE Username = '$signup_username'");
				$row = mysqli_fetch_array($result);

				if($row["Username"] == $signup_username) {
					$arr = array('response' => 'Success', 'message' => 'Your account was created successfully.');
					echo json_encode($arr);
				} else {
					$arr = array('response' => 'Failure', 'message' => 'Unable to create your account, Please try again.');
					echo json_encode($arr);
				}
			}
		}
			break;
	case "login":
		$form_username = $_POST["username"];
		$form_password = $_POST["password"];
		
		// Select the database to use
		$result = mysqli_query($con,"SELECT * FROM table1 WHERE Username = '$form_username'");
		$row = mysqli_fetch_array($result);

		if ($row["Username"] == $form_username && $row["Password"] == $form_password) {
			$arr = array('response' => 'Success', 'message' => 'You are a validated user.');
			echo json_encode($arr);
		} else {
			$arr = array('response' => 'Failure', 'message' => 'Sorry, your credentials are not valid, Please try again.');
			echo json_encode($arr);
		}
		break;
}
?>