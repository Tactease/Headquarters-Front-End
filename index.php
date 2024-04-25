<?php
require 'headquarters.php';

// Start or resume session
session_start();

// Check if Headquarters object is stored in session
if (!isset($_SESSION['headquarters'])) {
    // If not, create a new Headquarters object and store it in session
    $_SESSION['headquarters'] = new Headquarters();
}

// Retrieve existing Headquarters object from session
$hq = $_SESSION['headquarters'];

// Check if class ID is provided in URL parameters
if (isset($_GET['classId'])) {
    // Retrieve class ID from URL parameters
    $classId = $_GET['classId'];
    #print("Class Id is $classId.\n");

    // Call selectClass method
    $hq->selectClass($classId);
}

// Show the main page
$hq->showMainPage();
//?//>