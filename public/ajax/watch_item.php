<?php
if(!require('ajax_header.php'))
{
    return;
}

if(!isset($_GET['watch']) || !isset($_GET['id']))
{
    writeJsonResponse(400, "Parameter missing.");
    return;
}

$watch = $_GET['watch'] === 'true';

$stmt;
if($watch)
{
    $stmt = $dbh->prepare("INSERT INTO user_watched_items (user_id, watchable_id) VALUES (:userid, :watchableid)");
}
else
{
    $stmt = $dbh->prepare("DELETE FROM user_watched_items WHERE user_id = :userid AND watchable_id = :watchableid");
}
$dbresult = $stmt->execute(['userid' => $_SESSION['userdata']['id'], 'watchableid' => $_GET['id']]);

if($dbresult === false)
{
    writeJsonResponse(500, makeDBErrorString($stmt));
    return;
}

writeJsonResponse(200, true);
return;
?>
