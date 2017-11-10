<?php
// First thing is to set the variables that the header script will use.
// You can provide these or not as necessary - the header script will use default values if you don't set them here.
$page_title = 'Example Page - Project Tracker';
$css_files = ['/css/your-special-stylesheet.css', '/css/your-stylesheet-2.css'];
$js_files = ['your_script.js', 'your_script2.js'];
// Note: the header script automatically includes site-wide CSS and JS, regardless of what you put in $css_files and $js_files.
// Use these arrays only to add page-specific CSS and JS.

// Next step: include the header script using require():
if(!require($_SERVER['DOCUMENT_ROOT'] . '/header.php'))
{
	// If the header script returns false, then there was a login or other error that the header script handled for you.
	// Don't render any content or do anything.
	return;
}

// Now the page is all yours. You can assume that someone is logged in; their information is stored in $_SESSION['userdata'].
echo "Hello, {$_SESSION['userdata']['username']}!";

// A DB connection is also provided.
$db_conn->query("SELECT * FROM projects");

?>

<!-- You can include HTML anywhere. The content you create (whether statically or using PHP echo) will be included inside the content div element. -->
<p>Here is some static HTML content in a paragraph element.</p>

<?php
// Lastly, include the footer script.
require($_SERVER['DOCUMENT_ROOT'] . '/footer.php');
?>