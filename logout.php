<?php
session_start();
include "config.php";

// Call the logout function to clear the user's identifier cookie and destroy the session
logout();

// Redirect to the index page
header("Location: index.php?logout=1");
exit; // Ensure script execution stops after redirection
