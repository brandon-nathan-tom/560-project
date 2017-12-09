<?php

session_start();

require($_SERVER['DOCUMENT_ROOT'] . '/../php/database.php');

if(!isset($login_required)) $login_required = true;

if($login_required && !require($_SERVER['DOCUMENT_ROOT'] . '/../php/login.php'))
{
    return;
}

?>