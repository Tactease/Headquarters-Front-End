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

// Display the form to select a class
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Class</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Select Class</h1>
    
    <!-- Existing classes form -->
    <form action="mainpage.php" method="post">
        <label for="classId">Enter Class ID:</label><br>
        <input type="number" id="classId" name="classId" class="form-control" required><br><br>
        <input type="submit" class="form-control-bar" value="Select Class"><br>
    </form>
    <a href="<?php echo URL; ?>mainpage.php">Back to main page</a><br>
    <!-- list of existing classes -->
    <h3>Existing Classes</h3>
    <?php
    // Retrieve unique depClass combinations from the headquarters object
    $uniqueClasses = $hq->getUniqueClasses();

    // Display each unique class in the list
    foreach ($uniqueClasses as $class) {
        echo "<p>Class ID: {$class['classId']} - Name: {$class['className']}</p>";
    }
    ?>

    <script src="scripts.js"></script>
</body>
</html>