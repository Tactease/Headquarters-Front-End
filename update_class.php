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
    <form action="index.php" method="get">
        <label for="classId">Enter Class ID:</label><br>
        <input type="number" id="classId" name="classId" class="form-control" required><br><br>
        <input type="submit" class="form-control-bar" value="Select Class">
    </form>

    <!-- Collapsible list of existing classes -->
    <button class="collapsible">Existing Classes</button>
    <div class="content">
        <?php
        // Retrieve existing classes from the headquarters object
        $existingClasses = $hq->getExistingClasses();

        // Display each class in the list
        foreach ($existingClasses as $class) {
            echo "<p>Class ID: {$class['classId']} - Name: {$class['className']}</p>";
        }
        ?>
    </div>

    <script src="scripts.js"></script>
</body>
</html>