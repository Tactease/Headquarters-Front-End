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
    <h1>Select Class</h1>
    
    <!-- Existing classes form -->
    <form action="mainpage.php" method="post">
        <label for="classId">Enter Class ID:</label><br>
        <input type="number" id="classId" name="classId" class="form-control" required><br><br>
        <input type="submit"  class="btn btn-primary" value="Select Class"><br>
    </form>
    <br>
    <a href="<?php echo URL; ?>mainpage.php" class="btn btn-secondary">Back to main page</a><br>
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
    </div>
    <script src="scripts.js"></script>
</body>
</html>