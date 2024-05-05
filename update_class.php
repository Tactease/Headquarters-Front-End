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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if class ID and name are provided in the form
    if (isset($_POST['classId']) && isset($_POST['newclassName']) && isset($_POST['hq_number']) && isset($_POST['hq_pw'])) {
        // Retrieve class ID and name from form data
        $classId = $_POST['classId'];
        $newclassName = $_POST['newclassName'];
        $hq_number = $_POST['hq_number'];
        $hq_pw = $_POST['hq_pw'];

        // Validate class ID and name
        if (!empty($classId) && $classId > 0 && !empty($newclassName) && !empty($hq_number) && !empty($hq_pw)) {
            // Verify admin credentials
            $loginResult = intval($hq->verifyLogin($hq_number, $hq_pw));
            if ($loginResult) {
                if (isset($_POST['deleteClass'])) {
                    // Call deleteClass method if "Delete Class" button is pressed
                    echo "calling the deleteClass function...";
                    $deleteSuccess = $hq->deleteClass($classId);

                    // Redirect back to main page
                    if ($deleteSuccess) {
                        header("Location: " . URL . "mainpage.php?classes=deleted");
                        exit;
                    }
                    header("Location: " . URL . "mainpage.php?classes=not-deleted");
                    exit; // Ensure script execution stops after redirection
                } else {
                    // Call updateClass method if "Update Class" button is pressed
                    echo "calling the updateClass function...";
                    $updateSuccess = $hq->updateClass($classId, $newclassName);

                    // Redirect back to main page
                    if ($updateSuccess) {
                        header("Location: " . URL . "mainpage.php?classes=updated");
                        exit;
                    }
                    header("Location: " . URL . "mainpage.php?classes=not-updated");
                    exit; // Ensure script execution stops after redirection
                }
            } else {
                // Incorrect admin credentials
                echo "Incorrect admin credentials. Please try again.";
            }
        } else {
            // Invalid form data, show error message
            $errorMessage = "Please enter valid data for class ID, class name, personal number, and password.";
        }
    } else {
        // Form data is incomplete, show error message
        $errorMessage = "Please fill in all the required fields.";
    }
}

// Display the form to select a class
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Class</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
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
        <input type="submit" class="btn btn-primary"  name="updateClass" value="Update Class">
        <input type="submit" class="btn btn-danger"  name="deleteClass" value="Delete Class">
    </form>
    <a href="<?php echo URL; ?>mainpage.php" class="btn btn-secondary">Back to main page</a><br>
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

    </div>
</body>
</html>