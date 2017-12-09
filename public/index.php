<?php
$js_files = ['/js/homepage.js'];
if(!require($_SERVER['DOCUMENT_ROOT'] . '/header.php'))
{
	return;
}

echo "Hello " . htmlspecialchars($_SESSION['userdata']['nickname']);
?>
<a href="/logout.php">Log out</a>

<table id="test-table">
<thead>
	<tr><th data-sourcecol='col1'>Column 1</th><th data-sourcecol='col2'>Column 2</th><th data-sourcecol='cb'>Callback</th></tr>
</thead>
<tbody>
	<!-- Will be filled by BNTLib. -->
</tbody>
</table>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/footer.php');
?>