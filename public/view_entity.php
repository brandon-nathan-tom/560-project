<?php
$entityViews = [
    'project' => [
        'view' => $_SERVER['DOCUMENT_ROOT'] . '/../php/views/project.php',
        'scripts' => ['/js/view_project.js']
        ],
    'organization' => [
        'view' => $_SERVER['DOCUMENT_ROOT'] . '/../php/views/organization.php',
        'scripts' => ['/js/view_organization.js']
        ],
    'contributor' => [
        'view' => $_SERVER['DOCUMENT_ROOT'] . '/../php/views/contributor.php',
        'scripts' => ['/js/view_contributor.js']
        ]
    ];
    
if(!isset($_GET['type']) || !isset($entityViews[$_GET['type']]))
{
    // Missing or invalid entity type.
    http_response_code(400);
    return;
}
$js_files = array_merge(['/js/view_entity.js'],
                        $entityViews[$_GET['type']]['scripts']);
                        
if(!require($_SERVER['DOCUMENT_ROOT'] . '/header.php'))
{
    return;
}

// Get basic entity data; specific view will get and display the rest via AJAX
$stmt = $entityBaseQueries[$_GET['type']]($dbh, $_GET['id']);
if(!$stmt->execute())
{
    http_response_code(500);
    echo "DB Error ({$stmt->errorInfo()[0]}): {$stmt->errorInfo()[2]}";
    require($_SERVER['DOCUMENT_ROOT'] . '/footer.php');
    return;
}

$entity = $stmt->fetch(PDO::FETCH_ASSOC);
if($project === false)
{
    http_response_code(404);
    echo "Not found";
    require($_SERVER['DOCUMENT_ROOT'] . '/footer.php');
    return;
}

// Now we can go ahead and render the page.

// Set up invisible element to pass information to JavaScript about the entity
echo '<span id="entity-info" style="display: none" ' .
        'data-entityid="' . $_GET['id'] . '" ' .
        'data-entitytype="' . $_GET['type'] . '"></span>';

echo '<h2 class="entity-title">' . htmlspecialchars($entity['name']) . "</h2>";
?>
 (<a id="entity-watch-control" href="#" data-iswatched="<?php echo ($entity['watched'] == true) ? 'true' : 'false'; ?>"></a>)
<?php

require($entityViews[$_GET['type']]['view']);

require($_SERVER['DOCUMENT_ROOT'] . '/footer.php');
?>