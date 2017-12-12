<?php
$page_title = 'Contributors - Project Tracker';

$js_files = ['/js/contributors.js'];

if(!require($_SERVER['DOCUMENT_ROOT'] . '/header.php'))
{
    return;
}
?>

<h2>View Contributors</h2>
<table id="contributors-table" class="stripe" data-searchentity="contributor">
<thead>
    <tr><th data-sourcecol="name" data-searchkey="name">Contributor Name</th>
        <th data-sourcecol="email" data-searchkey="email">Email</th>
        <th data-sourcecol="watch">Watch</th></tr>
</thead>
<tbody>
    <!-- Will be filled by BNTLib -->
</tbody>
</table>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/footer.php');
?>
