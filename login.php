<?php
session_start();
include "config.php";
require 'headquarters.php';

if (!isset($_SESSION["user_id"])) {
    echo "Warning - user_id not found!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h2>Login</h2>
    <form action="mainpage.php" method="post">
        <label for="personalNumber">Personal Number:</label><br>
        <input type="text" id="personalNumber" name="personalNumber" maxlength="7" required><br><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" maxlength="50" required><br><br>
        <input type="submit" class="btn btn-primary" value="Login">
    </form>
</body>
</html>