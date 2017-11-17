<?php
if(isset($_SESSION['userdata'])) return true;

$loginFailed = false;

if(isset($_POST['login_name']))
{
	$userData = tryLogin($dbh, $_POST['login_name'], $_POST['login_pass']);
	
	if($userData != null)
	{
		$_SESSION['userdata'] = $userData;
		return true;
	}
	$loginFailed = true;
}

function tryLogin($dbh, $username, $password)
{
	$loginStmt = $dbh->prepare('SELECT * FROM users WHERE nickname = :nickname');
	$loginParams = [
		':nickname' => $_POST['login_name'],
		];
	if($loginStmt->execute($loginParams) === false)
	{
		echo "ERROR (" . $loginStmt->errorInfo()[0] . "): " . $loginStmt->errorInfo()[2];
	}
	
	$userData = $loginStmt->fetch(PDO::FETCH_ASSOC);
	
	if($userData === false || password_verify($_POST['login_pass'], $userData['pw_hash']) === false) return null;
	
	return $userData;
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
	<tr><td></td><td><input type="submit" value="Login" /> <a href="/create_account.php">Create account</a></td>
	<tr><td></td><td><?php if($loginFailed) { ?><span style="color:red">Login failed.</span><?php } ?></td></tr>
</tbody>
</table>
</form>
</body>

<?php
return false;
?>