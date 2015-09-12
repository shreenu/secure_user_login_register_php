<?php
require_once __DIR__.'/user.php';
$user = new user();
$user->sec_session_start();
if($user->login_check()){
    if($user->logout()){
        header("location: index.php");
    }
}
else{
    //worng request
    header("location: index.php");
}