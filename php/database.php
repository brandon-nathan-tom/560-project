<?php
$dbh = new PDO('pgsql:host=localhost;dbname=project_tracker', "postgres", NULL);

if($dbh == null)
{
    echo "ERROR CREATING DBH";
}
?>
