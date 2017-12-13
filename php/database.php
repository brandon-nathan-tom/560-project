<?php
$dbh = new PDO('pgsql:host=localhost;dbname=project_tracker', "postgres", NULL);

if($dbh == null)
{
    echo "ERROR CREATING DBH";
}

//QUERIES

$entityBaseSql = [
    'project' => "SELECT p.*, o.name AS owner, NOT(uwi.user_id IS NULL) AS watched FROM projects p
                    LEFT JOIN organizations o ON p.owner_id = o.id
                    LEFT JOIN (SELECT * FROM user_watched_items WHERE user_id = :userid) uwi
                        ON p.id = uwi.watchable_id",
    'organization' => "SELECT o.*, COALESCE(p.num_projects, 0) AS num_projects, NOT(uwi.user_id IS NULL) AS watched FROM organizations o
                        LEFT JOIN (SELECT owner_id, COUNT(*) AS num_projects FROM projects GROUP BY owner_id) p
                            ON o.id = p.owner_id
                        LEFT JOIN (SELECT * FROM user_watched_items WHERE user_id = :userid) uwi
                            ON o.id = uwi.watchable_id",
    'contributor' => "SELECT c.*, NOT(uwi.user_id IS NULL) AS watched FROM contributors c
                        LEFT JOIN (SELECT * FROM user_watched_items WHERE user_id = :userid) uwi
                            ON c.id = uwi.watchable_id"
    ];
    
$entityBaseBindings = [
    'project' => ['userid' => $_SESSION['userdata']['id']],
    'organization' => ['userid' => $_SESSION['userdata']['id']],
    'contributor' => ['userid' => $_SESSION['userdata']['id']]
    ];

/**
 * Without the id parameter, returns a prepared statement that gets all projects.
 * With the parameter, returns a prepared statement that gets the specified project by ID.
 */
function getProjectQuery($dbh, $id = -1)
{
    global $entityBaseSql, $entityBaseBindings;
    $sql = $entityBaseSql['project'];
    $bindings = $entityBaseBindings['project'];
                    
    if($id !== -1)
    {
        $sql .= " WHERE p.id = :id";
        $bindings['id'] = $id;
    }
    
    $stmt = $dbh->prepare($sql);
    bindValues($stmt, $bindings);
    
    return $stmt;
}

/**
 * Without the id parameter, returns a prepared statement that gets all organizations.
 * With the parameter, returns a prepared statement that gets the specified organization by ID.
 */
function getOrganizationQuery($dbh, $id = -1)
{
    global $entityBaseSql, $entityBaseBindings;
    $sql = $entityBaseSql['organization'];
    $bindings = $entityBaseBindings['organization'];
                    
    if($id !== -1)
    {
        $sql .= " WHERE o.id = :id";
        $bindings['id'] = $id;
    }
    
    $stmt = $dbh->prepare($sql);
    bindValues($stmt, $bindings);
    
    return $stmt;
}

/**
 * Without the id parameter, returns a prepared statement that gets all organizations.
 * With the parameter, returns a prepared statement that gets the specified organization by ID.
 */
function getContributorQuery($dbh, $id = -1)
{
    global $entityBaseSql, $entityBaseBindings;
    $sql = $entityBaseSql['contributor'];
    $bindings = $entityBaseBindings['contributor'];
                    
    if($id !== -1)
    {
        $sql .= " WHERE c.id = :id";
        $bindings['id'] = $id;
    }
    
    $stmt = $dbh->prepare($sql);
    bindValues($stmt, $bindings);
    
    return $stmt;
}

// Entity types, mapping to functions of ($dbh, $id) that give a base query for each.
$entityBaseQueries = [
    'project' => getProjectQuery,
    'organization' => getOrganizationQuery,
    'contributor' => getContributorQuery
    ];
    
// Entity types, mapping to subqueries available for each.
// Subqueries should use exactly one parameter (':id').
// (I might make these work like the base queries if the need arises.)
$entitySubqueries = [
    'project' =>
        ['dl_mirrors' => 'SELECT * FROM websites w JOIN download_mirrors dm ON w.id = dm.site_id WHERE dm.project_id = :id',
         'contributors' => 'SELECT * FROM contributors c JOIN project_contributors pc ON c.id = pc.contributor_id WHERE pc.project_id = :id',
         'websites' => 'SELECT * FROM websites w JOIN watchables_sites ws ON w.id = ws.site_id WHERE ws.watchable_id = :id',
         'licenses' => 'SELECT * FROM licenses l JOIN project_licenses pl ON l.id = pl.license_id JOIN websites w on l.text_link = w.id WHERE pl.project_id = :id'],
    'organization' =>
        ['members' => 'SELECT * FROM contributors c JOIN org_members om ON c.id = om.contributor_id WHERE om.org_id = :id',
         'projects' => 'SELECT * FROM projects WHERE owner_id = :id',
         'websites' => 'SELECT * FROM websites w JOIN watchables_sites ws ON w.id = ws.site_id WHERE ws.watchable_id = :id'],
    'contributor' =>
        ['organizations' => 'SELECT * FROM organizations o JOIN org_members om ON o.id = om.org_id WHERE om.contributor_id = :id',
         'projects' => 'SELECT * FROM projects p JOIN project_contributors pc ON p.id = pc.project_id WHERE pc.contributor_id = :id',
         'websites' => 'SELECT * FROM websites w JOIN watchables_sites ws ON w.id = ws.site_id WHERE ws.watchable_id = :id']
    ];
    
function getWatchersQuery($dbh, $entityId)
{
    $stmt = $dbh->prepare('SELECT u.* FROM users u JOIN user_watched_items uwi ON u.id = uwi.user_id WHERE uwi.watchable_id = :id');
    $stmt->bindValue('id', $entityId);
    return $stmt;
}

function bindValues($stmt, $bindings)
{
    foreach($bindings as $param => $value)
    {
        $stmt->bindValue($param, $value);
    }
}
?>
