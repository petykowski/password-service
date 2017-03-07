<?php

// Prepare for JSON Response
header('Content-Type: application/json');

// Access provided by database configuration file
require_once dirname(__DIR__).'/config.php';

//Connect to Database
$con = new mysqli($servername, $sqlusername, $sqlpassword, $sqldbname)
	or die("Unable to connect to MySQL");
	
switch ($_POST["action"]) {
	case "validate_username":
		$signup_username = $_POST["username"];
		$check = mysqli_query($con,"SELECT * FROM table1 WHERE Username = '$signup_username'");
		$check_row = mysqli_fetch_array($check);
		if($check_row["Username"] == $signup_username) {
			$arr = array('response' => 'Failure', 'message' => 'Username already exsists, please use different username.');
			echo json_encode($arr);
		}
		break;
	case "signup":
		$signup_username = $_POST["username"];
		$signup_password = $_POST["password"];

		if(empty($signup_username) || empty($signup_password)) {
			$arr = array('response' => 'Failure', 'message' => 'Username and password cannot be empty.');
			echo json_encode($arr);
		} else {
			$check = mysqli_query($con,"SELECT * FROM table1 WHERE Username = '$signup_username'");
			$check_row = mysqli_fetch_array($check);
			if($check_row["Username"] == $signup_username) {
				$arr = array('response' => 'Failure', 'message' => 'Username already exsists, please use different username.');
				echo json_encode($arr);
			} else {
				$hashpw = password_hash($signup_password, PASSWORD_DEFAULT);
				$insert = mysqli_query($con,"INSERT INTO table1 (Username, Password, Role) VALUES ('$signup_username', '$hashpw','user')");
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

		if ($row["Username"] == $form_username && password_verify($form_password, $row["Password"])) {
			$arr = array('response' => 'Success', 'message' => 'You are a validated user.');
			echo json_encode($arr);
		} else {
			$arr = array('response' => 'Failure', 'message' => 'Sorry, your credentials are not valid, Please try again.');
			echo json_encode($arr);
		}
		break;
}
?>