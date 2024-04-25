<?php
require 'headquarters.php';

// Retrieve existing Headquarters object from session
$hq = $_SESSION['headquarters'];

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