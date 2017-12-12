<?php
if(!require('ajax_header.php'))
{
    return;
}

$watchables = ['organization' => 'organizations', 
               'project' => 'projects',
               'contributor' => 'contributors'];
$response = [];

foreach($watchables as $entity => $table)
{
    $stmt = $dbh->prepare("SELECT w.* FROM {$table} w
                            JOIN user_watched_items uwi ON w.id = uwi.watchable_id
                            WHERE uwi.user_id = :userid");
    if(!$stmt->execute(['userid' => $_SESSION['userdata']['id']]))
    {
        writeJsonResponse(500, makeDBErrorString($stmt));
        return;
    }
    
    $response[$entity] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

writeJsonResponse(200, $response);
return;
?>