<?php
session_start();
include "config.php";
require 'headquarters.php';

if (!isset($_SESSION["user_id"])) {
    echo "Warning - user_id not found!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form action="mainpage.php" method="post">
        <label for="personalNumber">Personal Number:</label><br>
        <input type="text" id="personalNumber" name="personalNumber" required><br><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" value="Login">
    </form>
</body>
</html>