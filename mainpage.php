<?php
session_start();
include "config.php";
require 'headquarters.php';

$user_id_from_cookie = getUserIdFromCookie();

if (!$user_id_from_cookie) {
    //check whether login credentials are present
    if (!isset($_POST['personalNumber']) || !isset($_POST['password'])) {
        header("Location: index.php?error=noid");
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
        setUserIdCookie($personalNumber);
        echo "Login successful.";
        $_SESSION["user_id"] = $personalNumber;
        //echo "your id is " . $_SESSION["user_id"] . " !";
    } else {
        // Authentication failed, redirect back to login page with error message
        header("Location: index.php?badlogin=" . $loginResult);
        exit; // Ensure that no further code is executed after the redirection
    }
}
// Retrieve personalNumber from session
$personalNumber = $user_id_from_cookie;

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

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Headquarters - Main Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="public/TactEaseLogo.png" alt="Logo" height="200">
        </div>
        <div class="header-main-text">
            <h2>The Headquarters</h2>
        </div>
        <div class="user-avatar">
            <img src="public/userDefault.png" alt="User Avatar" height="150">
        </div>
    </header>
    <br>
    <!-- Show the main page -->
    <?php $hq->showMainPage();
    ?>
</body>
</html>