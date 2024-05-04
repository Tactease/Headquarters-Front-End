<?php
session_start();
include "config.php";
require 'headquarters.php';

if (!isset($_SESSION["user_id"])) {
    //check whether login credentials are present
    if (!isset($_POST['personalNumber']) || !isset($_POST['password'])) {
        header("Location: login.php?error=nodatalogin" . $_SESSION["user_id"]);
        exit; // Ensure that no further code is executed after the redirection
    }
    // Retrieve personalNumber and password from form submission
    $personalNumber = $_POST['personalNumber'];
    $password = $_POST['password'];

    $hq1 = new Headquarters($personalNumber);

    // Verify login credentials
    $loginResult = intval($hq1->verifyLogin($personalNumber, $password));

    // Check login result
    if ($loginResult == 1) {
        // Authentication successful, store user data in session
        $_SESSION["user_id"] = $personalNumber;
        //echo "your id is " . $_SESSION["user_id"] . " !";
    } else {
        // Authentication failed, redirect back to login page with error message
        header("Location: login.php?error=" . $loginResult . "-login");
        exit; // Ensure that no further code is executed after the redirection
    }
}
// Retrieve personalNumber from session
$personalNumber = $_SESSION["user_id"];

// Check if Headquarters object is stored in session based on personalNumber
if (!isset($_SESSION[$personalNumber])) {
    // If not, create a new Headquarters object and store it in session
    $_SESSION[$personalNumber] = new Headquarters($personalNumber); // Pass personalNumber to Headquarters constructor if needed
}

// Retrieve existing Headquarters object from session
$hq = $_SESSION[$personalNumber];
if(!isset($hq)){
    echo "warning - HQ object not found.";
    // Create a new Headquarters object
    $hq = new Headquarters(0);
}

if(isset($_POST['classId'])){
    $classId = $_POST['classId'];
    $hq->selectClass($classId);
}

// Show the main page
$hq->showMainPage();