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
    $hq_number = $_POST['hq_number'];
    $hq_pw = $_POST['hq_pw'];    
    $loginResult = intval($hq->verifyLogin($hq_number, $hq_pw));
    if($loginResult){
    // Check if class ID and name are provided in the form
    if (isset($_POST['classId']) && isset($_POST['newclassName'])) {
        // Retrieve class ID and name from form data
        $classId = $_POST['classId'];
        $newclassName = $_POST['newclassName'];

        // Validate class ID and name
        if (!empty($classId) && $classId > 0 && !empty($newclassName)) {
            // Call selectClass method
            // $hq->selectClass($classId);

            $hq->updateClass($classId,$newclassName);

            // Redirect back to main page
            header("Location: index.php");
            exit; // Ensure script execution stops after redirection
        } else {
            // Invalid class ID or name, show error message
            $errorMessage = "Please enter a valid class ID and name.";
        }
    }

    // Check if delete class button is clicked
    if (isset($_POST['deleteClass']) && isset($_POST['classId'])) {
        // Retrieve class ID from form data
        $classId = $_POST['classId'];

        // Validate class ID
        if (!empty($classId) && $classId > 0) {
            // Check if the entered class ID matches any existing class
            $existingClasses = $hq->getUniqueClasses();
            $classToDelete = null;
            foreach ($existingClasses as $class) {
                if ($class['classId'] == $classId) {
                    $classToDelete = $class;
                    break;
                }
            }

            // Display confirmation dialog if class is found
            if ($classToDelete) {
                echo "<script>";
                echo "if (confirm('Are you sure want to delete class $classId named \'{$classToDelete['className']}\'?')) {";
                echo "  window.location.href = 'delete_class.php?classId=$classId';"; // Redirect to delete_class.php with class ID
                echo "} else {";
                echo "  alert('Class deletion canceled.');"; // Show alert if deletion is canceled
                echo "}";
                echo "</script>";
            } else {
                // Class not found, show error message
                $errorMessage = "Class with ID $classId not found.";
            }
        } else {
            // Invalid class ID, show error message
            $errorMessage = "Please enter a valid class ID.";
        }
    }
    }
    else {
        echo "Confirmation data incorrect.";
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
    
    <!-- Form to select or update class -->
    <form action="update_class.php" method="post">
        <label for="classId">Enter Class ID:</label><br>
        <input type="number" id="classId" name="classId" class="form-control" required><br>
        <label for="className">Enter Class Name:</label><br>
        <input type="text" id="newclassName" name="newclassName" class="form-control" required><br><br>
                
        <h4>Confirm admin data to perform action.</h4>
        <label for="pakal">Personal Number:</label><br>
        <input type="text" id="hq_number" name="hq_number" required><br><br>
        <label for="password">Password:</label><br>
        <input type="password" id="hq_pw" name="hq_pw" required><br><br>
        <input type="submit" class="form-control-bar" name="updateClass" value="Select/Update Class">
        <input type="submit" class="form-control-bar" name="deleteClass" value="Delete Class">
    </form>
    <a href='index.php'>Back to Main Page</a><br>
    <!-- list of existing classes -->
    <h3>Existing Classes</h3>
        <?php
        // Retrieve existing classes from the headquarters object
        $currentClasses = $hq->getUniqueClasses();
        // Display each class in the list
        foreach ($currentClasses as $class) {
            echo "<p>Class ID: {$class['classId']} - Name: {$class['className']}</p>";
        }
        ?>

    <script src="scripts.js"></script>
</body>
</html>