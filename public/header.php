<!DOCTYPE html>

<?php
if(!require($_SERVER['DOCUMENT_ROOT'] . '/../php/login.php'))
{
	return;
}

if(!isset($page_title)) $page_title = 'Project Tracker';
$css_files_master = ['/css/style-main.css'];
if(isset($css_files))
{
	$css_files_master = array_merge($css_files_master, $css_files);
}
$js_files_master = [];
if(isset($js_files))
{
	$js_files_master = array_merge($js_files_master, $js_files);
}
?>

<html>
<head>
	<title><?php echo $page_title; ?></title>
	<?php
	foreach($css_files_master as $css_file)
	{
	echo "<link rel='stylesheet' type='text/css' href='{$css_file}' />\r\n";
	}
	foreach($js_files_master as $js_file)
	{
		echo "<script type='text/javascript' src='{$js_file}></script>\r\n";
	}
	?>
</head>

<body>
<div id="header">
<h1>BNT Project Tracker</h1>
</div>
