<?php
if(!require('ajax_header.php'))
{
    return;
}

$entitySearchFields = [
    'project' => [
        'id' => makeExactCompare('id'),
        'name' => makeFuzzyCompare('p.name'),
        'repo' => makeFuzzyCompare('repo'),
        'short_desc' => makeFuzzyCompare('p.short_description'),
        'owner' => makeFuzzyCompare('o.name')
        ],
    'organization' => [
        'id' => makeExactCompare('id'),
        'name' => makeFuzzyCompare('name'),
        'short_desc' => makeFuzzyCompare('short_description'),
        'homepage' => makeFuzzyCompare('homepage')
        ],
    'contributor' => [
        'id' => makeExactCompare('id'),
        'name' => makeFuzzyCompare('name'),
        'email' => makeFuzzyCompare('email')
        ]
    ];

if(!isset($_GET['type']) || !isset($entitySearchFields[$_GET['type']]))
{
    writeJsonResponse(400, "Missing or invalid parameter.");
    return;
}

$entity = $_GET['type'];

$stmt = prepareStmt($dbh, $entityBaseSql[$entity], $entityBaseBindings[$entity], $entitySearchFields[$entity]);
if(!$stmt->execute())
{
    writeJsonResponse(500, makeDBErrorString($stmt));
    return;
}

writeJsonResponse(200, $stmt->fetchAll(PDO::FETCH_ASSOC));
return;

// HELPER FUNCTIONS

function prepareStmt($dbh, $baseSql, $baseBindings, $searchFields)
{
    $sql = $baseSql;
    $bindings = $baseBindings;
    
    $firstCondition = true;
    foreach($searchFields as $col => $fn)
    {
        if(isset($_GET[$col]))
        {
            $sql .= $firstCondition ? ' WHERE ' : ' AND ';
            $fn($sql, $bindings, $_GET[$col]);
            $firstCondition = false;
        }
    }
    
    $stmt = $dbh->prepare($sql);
    foreach($bindings as $col => $val)
    {
        $stmt->bindValue($col, $val);
    }
    return $stmt;
}

function makeExactCompare($col)
{
    return function (&$sql, &$bindings, $val) use ($col) {
        $placeholder = makeNamedPlaceholder($col);
        $sql .= "{$col} = :{$placeholder}";
        $bindings[$placeholder] = $val;
    };
}

function makeFuzzyCompare($col)
{
    return function (&$sql, &$bindings, $val) use ($col) {
        $placeholder = makeNamedPlaceholder($col);
        $sql .= "{$col} ILIKE :{$placeholder}";
        $bindings[$placeholder] = "%{$val}%";
    };
}

?>