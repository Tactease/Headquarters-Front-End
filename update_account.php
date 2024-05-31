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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if class ID and name are provided in the form
    if (isset($_POST['personalNumber']) && isset($_POST['newAccountName']) && $user_id_from_cookie && isset($_POST['hq_pw'])) {
        // Retrieve class ID and name from form data
        $accountId = $_POST['personalNumber'];
        $newAccName = $_POST['newAccountName'];
        $hq_number = $user_id_from_cookie;
        $hq_pw = $_POST['hq_pw'];

        // Validate class ID and name
        if (!empty($accountId) && $accountId > 0 && !empty($hq_number) && !empty($hq_pw)) {
            // Verify admin credentials
            $loginResult = intval($hq->verifyLogin($hq_number, $hq_pw));
            if ($loginResult == 1) {
                if (isset($_POST['deleteAccount'])) {
                    if(!empty($newAccName)){
                    // Call deleteClass method if "Delete Class" button is pressed
                    echo "calling the deleteAccount function...";
                    $deleteSuccess = $hq->deleteSoldier($accountId);

                    // Redirect back to main page
                    if ($deleteSuccess) {
                        header("Location: " . URL . "update_account.php?account=deleted");
                        exit;
                    }
                    header("Location: " . URL . "update_account.php?account=not-deleted");
                    exit; // Ensure script execution stops after redirection
                    }
                    else {
                        header("Location: " . URL . "update_account.php?account=name-empty");
                        exit; // Ensure script execution stops after redirection                        
                    }
                } else {
                    // Call updateClass method if "Update Class" button is pressed
                    echo "calling the updateSoldier function...";
                    $updateSuccess = $hq->updateSoldier($accountId, $newAccName);

                    // Redirect back to main page
                    if ($updateSuccess) {
                        header("Location: " . URL . "update_account.php?account=updated");
                        exit;
                    }
                    header("Location: " . URL . "update_account.php?account=not-updated");
                    exit; // Ensure script execution stops after redirection
                }
            } else {
                // Incorrect admin credentials
                handle_verify_error($loginResult);
            }
        } else {
            // Invalid form data, show error message
            $errorMessage = "Please enter valid data for soldier number, soldier name, personal number, and password.";
        }
    } else {
        // Form data is incomplete, show error message
        $errorMessage = "Please fill in all the required fields.";
    }
}

if(isset($_GET['account'])){
    $actStatus = $_GET['account'];
    switch ($actStatus):
        case "deleted":
            echo "Account deleted successfully";
            break;
        case "not-deleted":
            echo "Account not deleted - not found in DB";
            break;
        case "updated":
            echo "Account updated successfully";
            break;
        case "not-updated":
            echo "Account not updated - not found in DB";
            break;
        case "name-empty":
            echo "Account not updated - name not provided properly";
            break;            
        default:
            echo "Warning - unknown action status";
            break;
    endswitch;
}

// Display the form to select an account
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Account</title>
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
    <h1>Select Account to Update</h1>
    
    <!-- Form to select or update account -->
    <form action="update_account.php" method="post">
        <label for="classId">Enter Account personal Number:</label><br>
        <input type="number" id="personalNumber" name="personalNumber" class="form-control" maxlength="7" required><br>
        <label for="className">Enter Account Name:</label><br>
        <input type="text" id="newAccountName" name="newAccountName" class="form-control" maxlength="50" required><br><br>
                
        <h4>Confirm admin data to perform action.</h4>
        <label for="password">Password:</label><br>
        <input type="password" id="hq_pw" name="hq_pw" maxlength="50" required><br><br>
        <input type="submit" class="btn btn-primary"  name="updateAccount" value="Update Account">
        <input type="submit" class="btn btn-danger"  name="deleteAccount" value="Delete Account">
    </form>
    <br>
    <a href="<?php echo URL; ?>mainpage.php" class="btn btn-secondary">Back to main page</a><br>
    </div>
</body>
</html>