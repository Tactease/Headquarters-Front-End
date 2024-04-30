<?php
// Include the headquarters.php file to access the Headquarters class
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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if class ID is provided in the form
    if (isset($_POST['classId'])) {
        // Retrieve class ID from form data
        $classId = $_POST['classId'];

        // Validate class ID
        if (!empty($classId) && $classId > 0) {
            // Call selectClass method
            $hq->selectClass($classId);

            // Redirect back to main page
            header("Location: index.php");
            exit; // Ensure script execution stops after redirection
        } else {
            // Invalid class ID, show error message
            $errorMessage = "Please enter a valid class ID.";
        }
    }
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
    <form action="" method="post">
        <label for="classId">Enter Class ID:</label><br>
        <input type="number" id="classId" name="classId" class="form-control" required><br><br>
        <input type="submit" class="form-control-bar" value="Select Class"><br>
    </form>
    <a href='index.php'>Back to Main Page</a><br>
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