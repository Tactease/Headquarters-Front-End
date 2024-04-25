<?php
// Include the headquarters.php file to access the Headquarters class
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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
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
        $hq->createSoldier($personalNumber, $fullName, $pakal, $password);
        echo "Soldier created successfully!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
</head>
<body>
    <h1>Create Account</h1>
    <?php if (isset($errorMessage)) : ?>
        <p style="color: red;"><?php echo $errorMessage; ?></p>
    <?php endif; ?>
    <form action="create_account.php" method="post">
        <label for="personal_number">Personal Number:</label><br>
        <input type="text" id="personal_number" name="personal_number" required><br><br>
        <label for="full_name">Full Name:</label><br>
        <input type="text" id="full_name" name="full_name" required><br><br>
        <label for="pakal">Pakal:</label><br>
        <input type="text" id="pakal" name="pakal" required><br><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" value="Create Account">
    </form>
    <a href='index.php'>Back to main page</a><br>
</body>
</html>