<?php
if(!require($_SERVER['DOCUMENT_ROOT'] . '/header.php'))
{
    return;
}

if(isset($_POST['github_repo']))
{
    set_time_limit(300);
    $githubRepo = escapeshellarg($_POST['github_repo']);
    // TOM
    $importCommand = "cd /home/niebie/sc/project-tracker/tools/recurse-org 2>&1 &&
                        ./recurse-org {$githubRepo} > temp.sql &&
                        /gnu/store/199m2kns4d79sysi174w2iqsh5qh9803-profile/bin/psql -U postgres -d project_tracker -f temp.sql 2>&1";
    $output = [];
    exec( $importCommand, $output );
    foreach($output as $line)
    {
        echo htmlspecialchars($line) . '<br />';
    }
    echo '---Repository imported. We think.';
}
?>

<form method="post">
GitHub Repository:
<input type="text" name="github_repo" required />
<input type="submit" value="Import" />
</form>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/footer.php');
?>