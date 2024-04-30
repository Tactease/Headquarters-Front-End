<?php
// Ensure session is started before any output
session_start();

require 'headquarters.php';

if (!isset($_SESSION['hq_object'])) {
// Retrieve personalNumber and password from form submission
$personalNumber = $_POST['personalNumber'];
$password = $_POST['password'];

// Create a new Headquarters object
$hq = new Headquarters(0);

// Verify login credentials
$loginResult = intval($hq->verifyLogin($personalNumber, $password));

// Check login result
if ($loginResult == true) {
    // Authentication successful, store user data in session
    $_SESSION['personalNumber'] = $personalNumber;
} else {
    // Authentication failed, redirect back to login page with error message
    header("Location: login.php?error=badlogin");
    exit; // Ensure that no further code is executed after the redirection
}

// Check if personalNumber is stored in session
if (!isset($_SESSION['personalNumber'])) {
    // If not, redirect the user to the login page
    header("Location: login.php?error=nonumber");
    exit; // Ensure that no further code is executed after the redirection
}

// Retrieve personalNumber from session
$personalNumber = $_SESSION['personalNumber'];

// Check if Headquarters object is stored in session based on personalNumber
if (!isset($_SESSION['hq_object'])) {
    // If not, create a new Headquarters object and store it in session
    $_SESSION['hq_object'] = new Headquarters($personalNumber); // Pass personalNumber to Headquarters constructor if needed
}
}

// Retrieve existing Headquarters object from session
$hq = $_SESSION['hq_object'];

// Show the main page
$hq->showMainPage();