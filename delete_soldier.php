<?php
require 'headquarters.php';

// Start or resume session
session_start();

// Check if personalNumber is stored in session
if (!isset($_SESSION['personalNumber'])) {
    echo "Warning - no personalNumber found.";
    // If not, redirect the user to the login page
    //header("Location: login.php?error=nonumber");
    //exit; // Ensure that no further code is executed after the redirection
}

// Retrieve personalNumber from session
$personalNumber = $_SESSION['personalNumber'];

// Check if Headquarters object is stored in session based on personalNumber
if (!isset($_SESSION['hq_object'])) {
    echo "Warning - no HQ object found!<br>";
    // If not, create a new Headquarters object and store it in session
    $_SESSION['hq_object'] = new Headquarters($personalNumber); // Pass personalNumber to Headquarters constructor if needed
}

// Retrieve existing Headquarters object from session
$hq = $_SESSION['hq_object'];

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