<?php
$page_title = 'Organizations - Project Tracker';

$js_files = ['/js/organizations.js'];

if(!require($_SERVER['DOCUMENT_ROOT'] . '/header.php'))
{
    return;
}
?>

<h2>View Organizations</h2>
<table id="orgs-table" class="autosearch stripe" data-searchentity="organization">
<thead>
    <tr><th data-sourcecol="name" data-searchkey="name">Organization Name</th>
        <th data-sourcecol="homepage" data-searchkey="homepage">Primary Web Page</th>
        <th data-sourcecol="num_projects">Projects</th>
        <th data-sourcecol="short_description" data-searchkey="short_desc">Description</th>
        <th data-sourcecol="watch">Watch</th></tr>
</thead>
<tbody>
    <!-- Will be filled by BNTLib -->
</tbody>
</table>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/footer.php');
?>
