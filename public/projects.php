<?php
$page_title = 'Projects - Project Tracker';

$js_files = ['/js/projects.js'];

if(!require($_SERVER['DOCUMENT_ROOT'] . '/header.php'))
{
    return;
}
?>

<h2>View Projects</h2>
<table id="projects-table" class="stripe" data-searchentity="project">
<thead>
    <tr><th data-sourcecol="name" data-searchkey="name">Project Name</th>
        <th data-sourcecol="repo" data-searchkey="repo">Repository</th>
        <th data-sourcecol="owner" data-searchkey="owner">Maintainer</th>
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
