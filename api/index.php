<?php

// Prepare for JSON Response
header('Content-Type: application/json');

// Access provided by database configuration file
require_once dirname(__DIR__).'/config.php';

// Format and Sanitize Username
$username = strtolower($_POST["username"]);
$sanitized_username = filter_var($username, FILTER_SANITIZE_EMAIL);

$password = $_POST["password"];

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
		$check = mysqli_query($con,"SELECT * FROM table1 WHERE Username = '$username'");
		$check_row = mysqli_fetch_array($check);

		// Return error if provided signup username exsists in database
		if($check_row["Username"] == $username) {
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
		
		$check = mysqli_query($con,"SELECT * FROM table1 WHERE Username = '$username'");
		$check_row = mysqli_fetch_array($check);
		
		// Return error if provided signup username exsists in database
		if($check_row["Username"] == $username) {
			$arr = array('response' => 'Failure', 'message' => 'Username already exsists, please use different username.');
			echo json_encode($arr);
			break;
		} else {
		// Hash password and attempt to create user account
			$hashpw = password_hash($password, PASSWORD_DEFAULT);
			$insert = mysqli_query($con,"INSERT INTO table1 (Username, Password, Role) VALUES ('$username', '$hashpw','user')");
			$result = mysqli_query($con,"SELECT * FROM table1 WHERE Username = '$username'");
			$row = mysqli_fetch_array($result);

			// Verifies against database that account was created.
			if($row["Username"] == $username) {
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
		$result = mysqli_query($con,"SELECT * FROM table1 WHERE Username = '$username'");
		$row = mysqli_fetch_array($result);
		
		if ($row["Username"] == $username && password_verify($password, $row["Password"])) {
			$arr = array('response' => 'Success', 'message' => 'You are a validated user.');
			echo json_encode($arr);
		} else {
			$arr = array('response' => 'Failure', 'message' => 'Sorry, your credentials are not valid, Please try again.');
			echo json_encode($arr);
		}
		break;
}
?>