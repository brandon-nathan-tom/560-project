<?php
$js_files = ['/js/homepage.js'];
if(!require($_SERVER['DOCUMENT_ROOT'] . '/header.php'))
{
	return;
}

echo "Hello " . htmlspecialchars($_SESSION['userdata']['nickname']);
?>
<a href="/logout.php">Log out</a>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/footer.php');
?>