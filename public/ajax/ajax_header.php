<?php

session_start();

require($_SERVER['DOCUMENT_ROOT'] . '/../php/database.php');

if(!isset($login_required)) $login_required = true;

if($login_required && !require($_SERVER['DOCUMENT_ROOT'] . '/../php/login.php'))
{
    return;
}

// Helper functions

function writeJsonResponse($code, $obj)
{
    header('Content-Type: application/json', true, $code);
    echo json_encode($obj);
}

function makeDBErrorString($db)
{
    $errorInfo = $db->errorInfo();
    return "DB Error ({$errorInfo[0]}): {$errorInfo[2]}";

}
	
function makeNamedPlaceholder($col)
{
    return str_replace(['.'], '_', $col);
}

?>