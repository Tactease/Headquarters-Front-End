<?php
// Include the Headquarters class
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            header("Location: index.php");
            exit;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Class</title>
</head>
<body>
    <h1>Create Class</h1>
    <?php if (isset($errorMessage)) : ?>
        <p style="color: red;"><?php echo $errorMessage; ?></p>
    <?php endif; ?>
<form action="create_class.php" method="post" autocomplete="on">
    <label>Class Name:
        <input type="text" name="class_name" class="form-control" required>
    </label><br><br>
    <label>Commander's Personal Number:
        <input type="text" name="commander_number" class="form-control" required>
    </label><br><br>
    <label>Number of Soldiers (1 to 65):
        <input type="number" name="num_soldiers" class="form-control" min="1" max="65" required>
    </label><br><br>

    <!-- Soldier Input Fields -->
    <?php for ($i = 0; $i < 65; $i++): ?>
        <label>Soldier <?= $i + 1 ?> Personal Number:
            <input type="text" name="soldier_number_<?= $i ?>" class="form-control">
        </label><br><br>
    <?php endfor; ?>

    <input type="submit" class="form-control-bar" id="form_submit_btn" value="Submit New Class">               
</form>
</body>
</html>