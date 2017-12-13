<?php
if(!require('ajax_header.php'))
{
    return;
}

if(!isset($_GET['id']) || !isset($_GET['type']) || !isset($entityBaseQueries[$_GET['type']]))
{
    writeJsonResponse(400, "Missing or invalid parameter.");
    return;
}

$stmt = $entityBaseQueries[$_GET['type']]($dbh, $_GET['id']);
if(!$stmt->execute())
{
    writeJsonResponse(500, makeDBErrorString($stmt));
    return;
}

$entity = $stmt->fetch(PDO::FETCH_ASSOC);
if(isset($_GET['appends']) && is_array($_GET['appends']))
{
    $subqueries = $entitySubqueries[$_GET['type']];
    
    foreach($subqueries as $property => $query)
    {
        // If this property wasn't requested, skip
        if(!in_array($property, $_GET['appends'])) continue;
        
        $stmt = $dbh->prepare($query);
        if(!$stmt->execute(['id' => $_GET['id']]))
        {
            writeJsonResponse(500, makeDBErrorString($stmt));
            return;
        }

        $entity[$property] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

writeJsonResponse(200, $entity);
return;
?>