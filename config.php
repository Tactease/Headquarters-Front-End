<?php
define("URL", "http://localhost:3000/");
//define("URL", "http://localhost/web1/php/posts-login-session/2022/");

function is_valid_id($userid){
    if(isset($userid) && $userid >= $_ENV['MIN_S_ID']){
        return 1;
    }
    return 0;
}