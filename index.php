<?php
session_start();
include "config.php";
require 'headquarters.php';

//check if user_id is valid in the session.
if (isset($_SESSION["user_id"]) && is_valid_id($_SESSION["user_id"])) {
    header("Location: mainpage.php");
    exit;
}

// else redirect to login
$_SESSION["user_id"] = -rand($_ENV['MIN_S_ID'],$_ENV['MAX_S_ID']);
header("Location: login.php?error=initlogin");
exit;