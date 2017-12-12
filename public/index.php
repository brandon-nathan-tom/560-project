<?php
$css_files = ['/css/homepage.css'];
$js_files = ['/js/homepage.js'];
if(!require($_SERVER['DOCUMENT_ROOT'] . '/header.php'))
{
    return;
}
?>
<h2>Quick Views</h2>
<div class="quick-view-container">
    <h3>Your Watched Organizations</h3>
    <table class="quick-watch stripe" data-watchentity="organization">
    <thead>
        <tr><th data-sourcecol="name">Organization Name</th>
            <th data-sourcecol="homepage">Home Page</th>
            <th data-sourcecol="watch">Watch</th></tr>
    </thead>
    <tbody>
        <!-- Will be filled by BNTLib -->
    </tbody>
    </table>
</div>

<div class="quick-view-container">
    <h3>Your Watched Projects</h3>
    <table class="quick-watch stripe" data-watchentity="project">
    <thead>
        <tr><th data-sourcecol="name">Project Name</th>
            <th data-sourcecol="repo">Repository</th>
            <th data-sourcecol="watch">Watch</th></tr>
    </thead>
    <tbody>
        <!-- Will be filled by BNTLib -->
    </tbody>
    </table>
</div>

<div class="quick-view-container">
    <h3>Your Watched Contributors</h3>
    <table class="quick-watch stripe" data-watchentity="contributor">
    <thead>
        <tr><th data-sourcecol="name">Contributor Name</th>
            <th data-sourcecol="email">Email</th>
            <th data-sourcecol="watch">Watch</th></tr>
    </thead>
    <tbody>
        <!-- Will be filled by BNTLib -->
    </tbody>
    </table>
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/footer.php');
?>