<?php
$login_required = false;
$page_title = "BNT - Create Account";

if(!require($_SERVER['DOCUMENT_ROOT'] . '/header.php'))
{
    return;
}

?>

<h2>Create Account</h2>

<?php
if(isset($_POST['username']))
{
    $accountStmt = $dbh->prepare("INSERT INTO users (nickname, name, email, pw_hash) VALUES (:nickname, :name, :email, :pw_hash);");
    $accountParams = [
        ':nickname' => $_POST['username'],
        ':name' => $_POST['name'],
        ':email' => $_POST['email'],
        ':pw_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT)
        ];
    if($accountStmt->execute($accountParams) !== true)
    {
        $errorCode = $accountStmt->errorInfo()[0];
        if($errorCode == 23505)
        {
            echo "<p>The username " . htmlspecialchars($_POST['username']) . " is already taken.</p>";
        }
        else
        {
            echo "<p>Could not create account (" . $accountStmt->errorInfo()[0] . "): " . htmlspecialchars($accountStmt->errorInfo()[2]) . "</p>";
        }
        displayForm();
    }
    else
    {
        echo "<p>Welcome, " . htmlspecialchars($_POST['username']) .  "! Your account has been created.</p>";
    }
}
else
{
    displayForm();
}

require($_SERVER['DOCUMENT_ROOT'] . '/footer.php');

function displayForm()
{
?>

<form id="account-form" method="post">
<table>
    <tr><td>Username:</td><td><input type="text" name="username" /></td></tr>
    <tr><td>Password:</td><td><input type="password" name="password" /></td></tr>
    <tr><td>Confirm password:</td><td><input type="password" name="confirmpassword" /></td></tr>
    <tr><td>Name:</td><td><input type="text" name="name" /></td></tr>
    <tr><td>Email address:</td><td><input type="text" name="email" /></td></tr>
    <tr><td></td><td><input type="submit" value="Create Account" />
</table>
</form>

<?php
}
?>