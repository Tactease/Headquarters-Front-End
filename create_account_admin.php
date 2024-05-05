<?php
session_start();
include "config.php";
require 'headquarters.php';

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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $hq_number = $_POST['hq_number'];
    //$hq_number = $_SESSION["user_id"];
    $hq_pw = $_POST['hq_pw'];    
    $loginResult = intval($hq->verifyLogin($hq_number, $hq_pw));
    if($loginResult){
    // Retrieve form data
    $personalNumber = $_POST['personal_number'];
    $fullName = $_POST['full_name'];
    $pakal = $_POST['pakal'];
    $password = $_POST['password'];

    // Validate form data
    if (empty($personalNumber) || empty($fullName) || empty($pakal) || empty($password)) {
        $errorMessage = "Error: All fields are required.";
    } elseif (!is_numeric($personalNumber)) {
        $errorMessage = "Error: Personal number must be a number.";
    } else {
        // Call createSoldier method to create a new soldier
        $hq->createAdmin($personalNumber, $fullName, $pakal, $password);
        //echo "Admin created successfully!";
    }

    }
    else {
        echo "Confirmation data incorrect.";
    }
    
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Create Admin Account</h1>
    <?php if (isset($errorMessage)) : ?>
        <p style="color: red;"><?php echo $errorMessage; ?></p>
    <?php endif; ?>
    <form action="#" method="post">
        <label for="personal_number">Personal Number:</label><br>
        <input type="text" id="personal_number" name="personal_number" required><br><br>
        <label for="full_name">Full Name:</label><br>
        <input type="text" id="full_name" name="full_name" required><br><br>
        <label for="pakal">Pakal:</label><br>
        <input type="text" id="pakal" name="pakal" required><br><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        <h4>Confirm admin data to perform action.</h4>
        <label for="pakal">Personal Number:</label><br>
        <input type="text" id="hq_number" name="hq_number" required><br><br>
        <label for="password">Password:</label><br>
        <input type="password" id="hq_pw" name="hq_pw" required><br><br>
        <input type="submit" class="btn btn-primary" value="Create Admin Account">
    </form>
    <a href="<?php echo URL; ?>mainpage.php" class="btn btn-secondary">Back to main page</a><br>
</body>
</html>