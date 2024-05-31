<?php
define("URL", "http://localhost:3000/");

function is_valid_id($userid){
    if(isset($userid) && $userid >= $_ENV['MIN_S_ID']){
        return 1;
    }
    return 0;
}

define("MAX_ATTEMPTS", 3);
define("LOCKOUT_DURATION", 3600);

function handle_verify_error($errorInput){
    $errorCode = intval($errorInput);
    switch($errorCode):
        case 0:
            echo "Admin Not Found";
            break;
        case 201:
            echo "Incorrect Password";
            break;
        case 202:
            echo "Incorrect Password - Attempt processing error";
            break;
        case 203:
            echo "Incorrect Password - Account locked due to failed verification attempts";
            break;
        case 204:
            echo "Incorrect Password - Account locking error";
            break;
        case 205:
            echo "Account lock expiration error";
            break;
        case 206:
            echo "Account locked due to recent failed verification attempts";
            break;
        default:
            echo "Warning - unknown verification error.";
            break;
        endswitch;
}

define("COOKIE_DURATION", 86400); //1 day
// Function to set a cookie containing the user's identifier
function setUserIdCookie($user_id) {
    // Set the cookie with a name, value, expiration time, and path
    setcookie("user_id", $user_id, time() + COOKIE_DURATION, "/"); // Expires after COOKIE_DURATION
}

// Function to retrieve the user's identifier from the cookie
function getUserIdFromCookie() {
    return $_COOKIE["user_id"] ?? null;
}

// Function to delete the user's identifier cookie
function deleteUserIdCookie() {
    // Set the cookie with a past expiration time to delete it
    setcookie("user_id", "", time() - 3600, "/");
}

// Logout action
function logout() {
    // Clear the user's identifier cookie
    deleteUserIdCookie();

    // Destroy the session data
    session_start();
    session_destroy();

}

// Error handling settings
ini_set('display_errors', 0); // Do not display errors
ini_set('log_errors', 1); // Log errors
ini_set('error_log', '/path/to/your/error.log'); // Specify the error log file
error_reporting(E_ALL); // Report all errors