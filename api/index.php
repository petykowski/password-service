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
		validateUsername($con, $_POST["username"]);
		break;
		
	case "signup":
		createUser($con, $_POST["username"], $_POST["password"]);
		break;

	case "login":
		validateUserCredentials($con, $_POST["username"], $_POST["password"]);
		break;
		
	case "profile":	
		retrieveUserProfile($con, $_POST["user_id"]);
		break;

	case "update-profile":	
		updateUserProfile($con, $_POST["user_id"], $_POST["fullname"], $_POST["bio"]);
		break;
}

/**
* Creates a New User
* $con: The SQL Server where the query will be executed.
* $user_name: The username to be assoicated to new account.
* $password: The password assoicated to the new account.
*/
function createUser($con, $user_name, $password)
{
		// Verifies no fields are empty
		if(empty($user_name) || empty($password)) {
			$arr = array('response' => 'Failure', 'message' => 'Username and password cannot be empty.');
			echo json_encode($arr);
			break;
		} 
		
		$sanitized_user_name = filter_var(strtolower($user_name), FILTER_SANITIZE_EMAIL);
		
		$check = mysqli_query($con,"SELECT * FROM USERS WHERE EMAIL = '$sanitized_user_name'");
		$check_row = mysqli_fetch_array($check);
		
		// Return error if provided signup username exsists in database
		if($check_row["EMAIL"] == $sanitized_user_name) {
			$arr = array('response' => 'Failure', 'message' => 'Username already exsists, please use different username.');
			echo json_encode($arr);
			break;
		} else {
			// Hash password and attempt to create user account
			$hashpw = password_hash($pass_word, PASSWORD_DEFAULT);
			$insert = mysqli_query($con,"INSERT INTO USERS (EMAIL, PASSWORD) VALUES ('$sanitized_user_name', '$hashpw')");
			$result = mysqli_query($con,"SELECT * FROM USERS WHERE EMAIL = '$sanitized_user_name'");
			$row = mysqli_fetch_array($result);

			// Verifies against database that account was created.
			if($row["EMAIL"] == $sanitized_user_name) {
				$arr = array('response' => 'Success', 'message' => 'Your account was created successfully.');
				echo json_encode($arr);
			} else {
				$arr = array('response' => 'Failure', 'message' => 'Unable to create your account, Please try again.');
				echo json_encode($arr);
			}
		}
}

/**
* Determines If Provided Username Already Exsists
* $con: The SQL Server where the query will be executed.
* $user_name: The username attempting to login.
*/
function validateUsername($con, $user_name)
{
		$sanitized_user_name = filter_var(strtolower($user_name), FILTER_SANITIZE_EMAIL);
		if($user_name != $sanitized_user_name){
			$arr = array('response' => 'Failure', 'message' => 'Username cannot contain special characters.');
			echo json_encode($arr);
			break;
		}
		// Request username from database
		$check = mysqli_query($con,"SELECT * FROM USERS WHERE EMAIL = '$user_name'");
		$check_row = mysqli_fetch_array($check);

		// Return error if provided signup username exsists in database
		if($check_row["EMAIL"] == $sanitized_user_name) {
			$arr = array('response' => 'Failure', 'message' => 'Username already exsists, please use different username.');
			echo json_encode($arr);
		}
}

/**
* Determines If Provided Username and Password Are Valid
* $con: The SQL Server where the query will be executed.
* $user_name: The username attempting to login.
* $password: The password assoicated to the login username.
*/
function validateUserCredentials($con, $user_name, $password)
{
		$user_name = filter_var(strtolower($user_name), FILTER_SANITIZE_EMAIL);
		$result = mysqli_query($con,"SELECT * FROM USERS WHERE EMAIL = '$user_name'");
		$row = mysqli_fetch_array($result);
		
		if ($row["EMAIL"] == $user_name && password_verify($password, $row["PASSWORD"])) {
			$arr = array('response' => 'Success', 'message' => 'You are a validated user.', 'user_id' => $row["USER_ID"]);
			echo json_encode($arr);
		} else {
			$arr = array('response' => 'Failure', 'message' => 'Sorry, your credentials are not valid, Please try again.');
			echo json_encode($arr);
		}
}

/**
* Returns All User Profile Details From Database
* $con: The SQL Server where the query will be executed.
* $id: The User ID of the profile to be returned.
*/
function retrieveUserProfile($con, $id)
{
		$result = mysqli_query($con,"SELECT * FROM PROFILE WHERE FK_USER_ID = '$id'");
		$row = mysqli_fetch_array($result);
		
		if ($result) {
			$arr = array('response' => 'Success', 'message' => 'Returning details of user profile.', 'fullname' => $row["FULL_NAME"], 'bio' => $row["BIO"]);
			echo json_encode($arr);
		} else {
			$arr = array('response' => 'Failure', 'message' => 'Unable to connect to server.');
			echo json_encode($arr);
		}
}

/**
* Updates User Profile Details on Database
* $con: The SQL Server where the query will be executed.
* $id: The User ID of the profile to be updated.
* $full_name: The full name of the user.
* $bio: A short description of the user.
*/
function updateUserProfile($con, $id, $full_name, $bio)
{
		$full_name = $con->real_escape_string($full_name);
		$bio = $con->real_escape_string($bio);
		$res = mysqli_query($con,"UPDATE PROFILE SET FULL_NAME='$full_name', BIO='$bio' WHERE FK_USER_ID=$id");
		if ($res) {
			$arr = array('response' => 'Success', 'fullname' => $full_name, 'bio' => $bio, 'user_id' => $user_id, 'query' => $res);
			echo json_encode($arr);
		} else {
			$arr = array('response' => 'Error', 'fullname' => $full_name, 'bio' => $bio, 'user_id' => $user_id, 'query' => $res);
			echo json_encode($arr);
		}
}

?>