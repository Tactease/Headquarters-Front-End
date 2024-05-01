<?php
require 'headquarters.php';

// Start or resume session
session_start();

// Check if personalNumber is stored in session
if (!isset($_SESSION["user_id"])) {
    // If not, redirect the user to the login page
    header("Location: login.php?error=noid");
    exit; // Ensure that no further code is executed after the redirection
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

// Check if soldier ID is provided in URL parameters
if (isset($_GET['soldierId'])) {
    // Retrieve soldier ID from URL parameters
    $soldierId = $_GET['soldierId'];

    // Call deleteSoldierFromCollection method
    $hq->deleteSoldierFromCollection($soldierId);
} else {
    // Redirect or display an error message if soldier ID is not provided
    // For example:
    // header("Location: index.php"); // Redirect to homepage
    echo "Soldier ID is missing.";
}