<?php

// Prepare client for JSON response
header('Content-Type: application/json');

// Load configuration file to access database
require_once dirname(__DIR__).'/config.php';

// Establish connection to database
$con = new mysqli($servername, $sqlusername, $sqlpassword, $sqldbname)
	or die("Unable to connect to MySQL");

switch ($_POST["action"]) {
	case "validate_username":
		validateUsername($con, $_POST["username"]);
		break;

	case "signup":
		createUser($con, $_POST["username"], $_POST["fullname"], $_POST["password"]);
		break;

	case "login":
		validateUserCredentials($con, $_POST["username"], $_POST["password"]);
		break;

	case "profile":	
		retrieveUserProfile($con, $_POST["user_id"]);
		break;

	case "update-profile":	
		updateUserProfile($con, $_POST["user_id"], $_POST["fullname"], $_POST["bio"], $_POST["photo"]);
		break;
		
	case "post":
		createNewPost($con, $_POST["filename"], $_POST["text"], $_POST["datetime"], $_POST["userid"]);
		break;
		
	case "get-posts":
		retrievePosts($con);
		break;
}


/**
* Determines If Provided Username Already Exsists
* $con: The SQL Server where the query will be executed.
* $user_name: The username attempting to login.
**/
function validateUsername($con, $user_name) {
	$sanitized_user_name = filter_var(strtolower($user_name), FILTER_SANITIZE_EMAIL);
	if($user_name != $sanitized_user_name){
		$arr = array('response' => 'Failure', 'code' => 10004, 'message' => 'Username cannot contain special characters.');
		echo json_encode($arr);
		break;
	}
	// Request username from database
	$check = mysqli_query($con,"SELECT * FROM USERS WHERE EMAIL = '$user_name'");
	$check_row = mysqli_fetch_array($check);

	// Return error if provided signup username exsists in database
	if($check_row["EMAIL"] == $sanitized_user_name) {
		$arr = array('response' => 'Failure', 'code' => 10003, 'message' => 'Username already exsists, please use different username.');
		echo json_encode($arr);
	}
}


/**
* Creates a New User
* $con: The SQL Server where the query will be executed.
* $user_name: The username to be assoicated to new account.
* $full_name: The full name to be assoicated to the new user account profile.
* $password: The password assoicated to the new account.
**/
function createUser($con, $user_name, $full_name, $password) {
	// Verifies no fields are empty
	if(empty($user_name) || empty($password)) {
		$arr = array('response' => 'Failure', 'code' => 10005, 'message' => 'Username and password cannot be empty.');
		echo json_encode($arr);
		break;
	} 
	
	$sanitized_user_name = filter_var(strtolower($user_name), FILTER_SANITIZE_EMAIL);
	$full_name = $con->real_escape_string($full_name);
	
	$check = mysqli_query($con,"SELECT * FROM USERS WHERE EMAIL = '$sanitized_user_name'");
	$check_row = mysqli_fetch_array($check);
	
	// Return error if provided signup username exsists in database
	if($check_row["EMAIL"] == $sanitized_user_name) {
		$arr = array('response' => 'Failure', 'code' => 10003, 'message' => 'Username already exsists, please use different username.');
		echo json_encode($arr);
		break;
	} else {
		// Hash password and attempt to create user account
		$hashpw = password_hash($password, PASSWORD_DEFAULT);
		$insert = mysqli_query($con,"INSERT INTO USERS (EMAIL, PASSWORD) VALUES ('$sanitized_user_name', '$hashpw')");
		$getID = mysqli_query($con, "SELECT LAST_INSERT_ID() AS USER_ID");
		$rowID = mysqli_fetch_array($getID);
		$user_id = $rowID["USER_ID"];
		$result = mysqli_query($con,"SELECT * FROM USERS WHERE EMAIL = '$sanitized_user_name'");
		$insertProfile = mysqli_query($con,"INSERT INTO PROFILE (FULL_NAME, FK_USER_ID) VALUES ('$full_name', '$user_id')");
		$row = mysqli_fetch_array($result);

		// Verifies against database that account was created.
		if($row["EMAIL"] == $sanitized_user_name) {
			$arr = array('response' => 'Success', 'message' => 'Your account was created successfully.', 'user_id' => $row["USER_ID"]);
			echo json_encode($arr);
		} else {
			$arr = array('response' => 'Failure', 'message' => 'Unable to create your account, Please try again.');
			echo json_encode($arr);
		}
	}
}


/**
* Determines If Provided Username and Password Are Valid
* $con: The SQL Server where the query will be executed.
* $user_name: The username attempting to login.
* $password: The password assoicated to the login username.
**/
function validateUserCredentials($con, $user_name, $password) {
	$user_name = filter_var(strtolower($user_name), FILTER_SANITIZE_EMAIL);
	$result = mysqli_query($con,"SELECT * FROM USERS WHERE EMAIL = '$user_name'");
	$row = mysqli_fetch_array($result);
	
	if ($row["EMAIL"] == $user_name && password_verify($password, $row["PASSWORD"])) {
		$arr = array('response' => 'Success', 'message' => 'You are a validated user.', 'user_id' => $row["USER_ID"]);
		echo json_encode($arr);
	} elseif ($row["EMAIL"] == $user_name) {
		$arr = array('response' => 'Failure', 'code' => 10001, 'message' => 'Sorry, your credentials are not valid, Please try again.');
		echo json_encode($arr);
	} elseif ($row["EMAIL"] != $user_name) {
		$arr = array('response' => 'Failure', 'code' => 10002, 'message' => 'Username not found. Please re-enter credentials and try again.');
		echo json_encode($arr);
	}
}


/**
* Returns All User Profile Details From Database
* $con: The SQL Server where the query will be executed.
* $id: The User ID of the profile to be returned.
**/
function retrieveUserProfile($con, $id) {
	$result = mysqli_query($con,"SELECT * FROM PROFILE WHERE FK_USER_ID = '$id'");
	$row = mysqli_fetch_array($result);

	if ($result) {
		$arr = array('response' => 'Success', 'message' => 'Returning details of user profile.', 'user_id' => $id, 'fullname' => $row["FULL_NAME"], 'bio' => $row["BIO"], 'photo' => $row["PICTURE"]);
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
**/
function updateUserProfile($con, $id, $full_name, $bio, $photo) {
	$full_name = $con->real_escape_string($full_name);
	$bio = $con->real_escape_string($bio);
	$res = mysqli_query($con,"UPDATE PROFILE SET FULL_NAME='$full_name', BIO='$bio', PICTURE='$photo' WHERE FK_USER_ID=$id");
	if ($res) {
		$arr = array('response' => 'Success', 'fullname' => $full_name, 'bio' => $bio, 'user_id' => $id, 'query' => $res);
		echo json_encode($arr);
	} else {
		$arr = array('response' => 'Error', 'fullname' => $full_name, 'bio' => $bio, 'user_id' => $id, 'query' => $res);
		echo json_encode($arr);
	}
}

/**
* Writes Contents of New Post Details To Database
* $con: The SQL Server where the query will be executed.
* $filename: The filename of the post.
* $text: This is the post text or description.
* $datetime: This is the time the post was created.
* $userid: User ID of the user who is creating a new post.
**/
function createNewPost($con, $filename, $text, $datetime, $userid) {
	$query = mysqli_query($con,"INSERT INTO POST (FILENAME, TEXT, DATE_TIME, FK_USER_ID) VALUES ($filename, $text, CURRENT_TIMESTAMP(), $userid)");
	if ($query) {
		$arr = array('response' => 'Success');
		echo json_encode($arr);
	} else {
		$arr = array('response' => 'Error');
		echo json_encode($arr);
	}
}

/**
* Writes Contents of New Post Details To Database
* $con: The SQL Server where the query will be executed.
* $filename: The filename of the post.
* $text: This is the post text or description.
* $datetime: This is the time the post was created.
* $userid: User ID of the user who is creating a new post.
**/
function retrievePosts($con) {
	$query = mysqli_query($con,"SELECT POST.*, PROFILE.FULL_NAME, PROFILE.PICTURE FROM POST JOIN PROFILE ON POST.FK_USER_ID = PROFILE.FK_USER_ID");
	$row = mysqli_fetch_all($query);
	
	// Prepare for loop
	$json = array();
	
	if ($query) {
		foreach ($query as $row) {
			$jsonBody = array(
				'filename' => (string) $row["FILENAME"],
				'text' => (string) $row["TEXT"],
				'datetime' => (string) $row["DATE_TIME"],
				'fullname' => (string) $row["FULL_NAME"],
				'picture' => (string) $row["PICTURE"]
			);
			$json[] = array('post' => $jsonBody);
		}
		// Free up memory and encode
		$query->free();
		echo json_encode($json);
	} else {
		$arr = array('response' => 'Error');
		echo json_encode($arr);
	}
}

?>