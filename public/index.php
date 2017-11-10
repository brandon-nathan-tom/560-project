<?php
if(!require($_SERVER['DOCUMENT_ROOT'] . '/header.php'))
{
	return;
}

echo "Hello " . htmlspecialchars($_SESSION['userdata']['username']);
?>
<a href="index.php?logout=true">Log out</a>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/footer.php');
?>