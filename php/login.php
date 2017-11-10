<?php
if(isset($_GET['logout']))
{
	unset($_SESSION['userdata']);
}

if(isset($_SESSION['userdata'])) return true;

$loginFailed = false;

if(isset($_POST['login_name']))
{
	$userData = tryLogin($_POST['login_name'], $_POST['login_pass']);
	
	if($userData != null)
	{
		$_SESSION['userdata'] = $userData;
		return true;
	}
	$loginFailed = true;
}

function tryLogin($username, $password)
{
	if($password !== "password") return null;
	return ['username' => $username];
}
?>

<html>
<head>
	<title>Login - Project Tracker</title>
</head>

<body>
<form method="post">
<table>
<tbody>
	<tr><td>Username:</td><td><input type="text" name="login_name" /></td></tr>
	<tr><td>Password:</td><td><input type="password" name="login_pass" /></td></tr>
	<tr><td></td><td><input type="submit" value="Login" /></td>
	<tr><td></td><td><?php if($loginFailed) { ?><span style="color:red">Login failed.</span><?php } ?></td></tr>
</tbody>
</table>
</form>
</body>

<?php
return false;
?>