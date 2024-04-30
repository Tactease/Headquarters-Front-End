<?php
// Include the headquarters.php file to access the Headquarters class
require 'headquarters.php';

// Start or resume session
session_start();
$personalNumber = 100;
// Check if personalNumber is stored in session
if (!isset($_SESSION['personalNumber'])) {
    echo "Warning - no personalNumber found.";
    // If not, redirect the user to the login page
    //header("Location: login.php?error=nonumber");
    //exit; // Ensure that no further code is executed after the redirection
}
else{
    // Retrieve personalNumber from session
$personalNumber = $_SESSION['personalNumber'];
}
// Check if Headquarters object is stored in session based on personalNumber
if (!isset($_SESSION['hq_object'])) {
    echo "Warning - no HQ object found!<br>";
    // If not, create a new Headquarters object and store it in session
    $_SESSION['hq_object'] = new Headquarters($personalNumber); // Pass personalNumber to Headquarters constructor if needed
}

// Retrieve existing Headquarters object from session
$hq = $_SESSION['hq_object'];

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $hq_number = $_POST['hq_number'];
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
        echo "Admin created successfully!";
    }

    }
    else {
        echo "Confirmation data incorrect.";
    }
    
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account</title>
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
        <input type="submit" value="Create Admin Account">
    </form>
    <a href='index.php'>Back to main page</a><br>
</body>
</html>