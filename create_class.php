<?php
session_start();
include "config.php";
require 'headquarters.php';

//check that the user is logged in
$user_id_from_cookie = getUserIdFromCookie();
if (!$user_id_from_cookie) {
    // If not, redirect the user to the login page
    header("Location: index.php?error=noid");
    exit; // Ensure that no further code is executed after the redirection
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

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hq_number = $user_id_from_cookie;
    $hq_pw = $_POST['hq_pw'];    
    $loginResult = intval($hq->verifyLogin($hq_number, $hq_pw));
    if($loginResult == 1){
    // Retrieve form data
    $className = $_POST['class_name'];
    $commanderNumber = $_POST['commander_number'];
    $numSoldiers = intval($_POST['num_soldiers']);

    // Array to store soldiers of the class
    $soldiers = [];

    // Check if class name is empty
    if (empty($className)) {
        $errorMessage = "Error: Class name cannot be empty.";
    } elseif (empty($commanderNumber)) {
        $errorMessage = "Error: Commander's personal number cannot be empty.";
    } elseif ($numSoldiers < 1 || $numSoldiers > 65) {
        $errorMessage = "Error: Number of soldiers must be between 1 and 65.";
    } else {
        // Loop to add soldiers to the class
        for ($i = 0; $i < $numSoldiers; $i++) {
            // Check if soldier number is empty
            $soldierNumber = $_POST["soldier_number_$i"];
            if (empty($soldierNumber)) {
                $errorMessage = "Error: Soldier's personal number cannot be empty.";
                break;
            }

            // Check if soldier exists
            if (!$hq->soldierExists($soldierNumber)) {
                $errorMessage = "Error: Soldier with personal number $soldierNumber doesn't exist in the system.";
                break;
            }

            // If soldier exists, add to the class
            $soldiers[] = intval($soldierNumber);
        }

        // If soldiers are added, create the class
        if (empty($errorMessage)) {
            // Save the class to the MongoDB collection
            $hq->createClass($className, $commanderNumber, $soldiers, $numSoldiers);
            header("Location: " . URL . "mainpage.php");
            exit;
        }
    }

    }
    else {
        handle_verify_error($loginResult);
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Class</title>
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
    <div class="container">
    <h1>Create Class</h1>
    <?php if (isset($errorMessage)) : ?>
        <p style="color: red;"><?php echo $errorMessage; ?></p>
    <?php endif; ?>
    <form action="#" method="post" autocomplete="on">
        <label>Class Name:
            <input type="text" name="class_name" class="form-control" maxlength="50" required>
        </label><br><br>
        <label>Commander's Personal Number:
            <input type="text" name="commander_number" class="form-control" maxlength="7" required>
        </label><br><br>
        <label>Number of Soldiers (1 to 65):
            <input type="number" name="num_soldiers" class="form-control" min="1" max="65" required>
        </label><br><br>

        <!-- Soldier Input Fields -->
        <?php for ($i = 0; $i < 65; $i++): ?>
            <label>Soldier <?= $i + 1 ?> Personal Number:
                <input type="text" name="soldier_number_<?= $i ?>" class="form-control" maxlength="7">
            </label><br><br>
        <?php endfor; ?> 

            <h4>Confirm admin data to perform action.</h4>
            <label for="password">Password:</label><br>
            <input type="password" id="hq_pw" name="hq_pw" maxlength="50" required><br><br>

        <input type="submit"  class="btn btn-primary" id="form_submit_btn" value="Submit New Class">               
    </form>
    <br>
    <a href="<?php echo URL; ?>mainpage.php" class="btn btn-secondary">Back to main page</a><br>
    </div>
</body>
</html>