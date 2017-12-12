<?php
if(!require('ajax_header.php'))
{
    return;
}

$entityUpdateTables = [
    'project' => 'projects',
    'organization' => 'organizations',
    'contributor' => 'contributors'
    ];
$entityUpdateFields = [
    'project' => [
        'name' => 'name',
        'repo' => 'repo',
        'short_desc' => 'short_description',
        'description'=> 'description',
        ],
    'organization' => [
        'name' => 'name',
        'short_desc' => 'short_description',
        'description' => 'description',
        'homepage' => 'homepage'
        ],
    'contributor' => [
        'name' => 'name',
        'email' => 'email'
        ]
    ];
    
if(!isset($_POST['type'])
    || !isset($entityUpdateTables[$_POST['type']])
    || !isset($_POST['id']))
{
    writeJsonResponse(400, "Missing or invalid parameter.");
    return;
}

$entity = $_POST['type'];
$fields = $entityUpdateFields[$entity];

$updateProperties = array_filter($_POST, function ($field) use ($fields) { return isset($fields[$field]); }, ARRAY_FILTER_USE_KEY);
if(count($updateProperties) === 0)
{
    writeJsonResponse(200, true);
    return;
}

$updateSql = "UPDATE \"{$entityUpdateTables[$entity]}\"";
$bindings = [];
$firstAppend = true;

foreach($updateProperties as $prop => $val)
{
    $placeholder = makeNamedPlaceholder($prop);
    $updateSql .= $firstAppend ? ' SET ' : ', ';
    $updateSql .= $fields[$prop] . ' = :' . $placeholder;
    $bindings[$placeholder] = $val;
    $firstAppend = false;
}

$updateSql .= " WHERE id = :id";
$bindings['id'] = $_POST['id'];

$stmt = $dbh->prepare($updateSql);
if(!$stmt->execute($bindings))
{
    writeJsonResponse(500, makeDBErrorString($stmt));
    return;
}

// See whether any rows were actually updated; if not, return.
if($stmt->rowCount() === 0)
{
    writeJsonResponse(200, false);
    return;
}

// Now email all users who watch this entity.
// A transaction is not necessary, since the entity should still be updated
// regardless of whether we can successfully notify the users.

// Get all the users to notify
$watchersStmt = getWatchersQuery($dbh, $_POST['id']);
if(!$watchersStmt->execute())
{
    writeJsonResponse(500, makeDBErrorString($watchersStmt));
    return;
}

// Get the full information from the entity that was updated
$getEntityStmt = $entityBaseQueries[$entity]($dbh, $_POST['id']);
if(!$getEntityStmt->execute())
{
    writeJsonResponse(500, makeDBErrorString($getEntityStmt));
    return;
}
$updatedEntity = $getEntityStmt->fetch(PDO::FETCH_ASSOC);

// Email all watchers
$mailHeaders = <<<EOD
From: brandon.nathan.tom@gmail.com
MIME-Version: 1.0
Content-Type: text/html; charset=iso-8859-1
EOD;

while(($watcher = $watchersStmt->fetch(PDO::FETCH_ASSOC)) !== false)
{
    $safeName = htmlspecialchars($watcher['name']);
    $safeEntityName = htmlspecialchars($updatedEntity['name']);
    $safeEntity = htmlspecialchars($entity);
    $messageBody = <<<EOD
<html>
<body>
<p>Hi, {$safeName}!</p>
<p><a href="http://70.179.164.247:{$_SERVER['SERVER_PORT']}/view_entity.php?type={$safeEntity}&id={$updatedEntity['id']}">{$safeEntityName}</a>
has been updated on BNT Project Tracker!</p>
</body>
</html>
EOD;
    echo 'Emailing ' . $watcher['email'];
    mail($watcher['email'], 'Notification - BNT Project Tracker', $messageBody, $mailHeaders);
}

writeJsonResponse(200, true);
return;

?>