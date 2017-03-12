<?php

// Prepare for JSON Response
header('Content-Type: application/json');

// Access provided by database configuration file
require_once dirname(__DIR__).'/config.php';

// Format and Sanitize Username
$username = strtolower($_POST["username"]);
$sanitized_username = filter_var($username, FILTER_SANITIZE_EMAIL);

$password = $_POST["password"];

$user_id = $_POST["user_id"];

//Connect to Database
$con = new mysqli($servername, $sqlusername, $sqlpassword, $sqldbname)
	or die("Unable to connect to MySQL");
	
switch ($_POST["action"]) {
	/**
	* Will check the provided signup username against the user database to check for exsisting entries.
	*/
	case "validate_username":
		if($username != $sanitized_username){
			$arr = array('response' => 'Failure', 'message' => 'Username cannot contain special characters.');
			echo json_encode($arr);
			break;
		}
		// Request username from database
		$check = mysqli_query($con,"SELECT * FROM USERS WHERE EMAIL = '$username'");
		$check_row = mysqli_fetch_array($check);

		// Return error if provided signup username exsists in database
		if($check_row["EMAIL"] == $username) {
			$arr = array('response' => 'Failure', 'message' => 'Username already exsists, please use different username.');
			echo json_encode($arr);
		}
		break;
	/**
	* Will attempt to create new account using provided signup username and password
	*/
	case "signup":
		// Verifies no fields are empty
		if(empty($username) || empty($password)) {
			$arr = array('response' => 'Failure', 'message' => 'Username and password cannot be empty.');
			echo json_encode($arr);
			break;
		} 
		
		$check = mysqli_query($con,"SELECT * FROM USERS WHERE EMAIL = '$username'");
		$check_row = mysqli_fetch_array($check);
		
		// Return error if provided signup username exsists in database
		if($check_row["EMAIL"] == $username) {
			$arr = array('response' => 'Failure', 'message' => 'Username already exsists, please use different username.');
			echo json_encode($arr);
			break;
		} else {
		// Hash password and attempt to create user account
			$hashpw = password_hash($password, PASSWORD_DEFAULT);
			$insert = mysqli_query($con,"INSERT INTO USERS (EMAIL, PASSWORD) VALUES ('$username', '$hashpw')");
			$result = mysqli_query($con,"SELECT * FROM USERS WHERE EMAIL = '$username'");
			$row = mysqli_fetch_array($result);

			// Verifies against database that account was created.
			if($row["EMAIL"] == $username) {
				$arr = array('response' => 'Success', 'message' => 'Your account was created successfully.');
				echo json_encode($arr);
			} else {
				$arr = array('response' => 'Failure', 'message' => 'Unable to create your account, Please try again.');
				echo json_encode($arr);
			}
		}
		break;
	/**
	* Compares username and password against database
	*/
	case "login":	
		$result = mysqli_query($con,"SELECT * FROM USERS WHERE EMAIL = '$username'");
		$row = mysqli_fetch_array($result);
		
		if ($row["EMAIL"] == $username && password_verify($password, $row["PASSWORD"])) {
			$arr = array('response' => 'Success', 'message' => 'You are a validated user.');
			echo json_encode($arr);
		} else {
			$arr = array('response' => 'Failure', 'message' => 'Sorry, your credentials are not valid, Please try again.');
			echo json_encode($arr);
		}
		break;
		
	case "profile":	
		$res = mysqli_query($con,"SELECT * FROM USERS WHERE EMAIL = '$username'");
		$resRow = mysqli_fetch_array($res);
		$user_id = $resRow["USER_ID"];
		$result = mysqli_query($con,"SELECT * FROM PROFILE WHERE FK_USER_ID = '$user_id'");
		$row = mysqli_fetch_array($result);
		
		$arr = array('response' => 'Success', 'fullname' => $row["FULL_NAME"], 'bio' => $row["BIO"]);
		echo json_encode($arr);
		break;
}
?>