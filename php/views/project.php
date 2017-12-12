<div id="project-main-info" data-entityid="<?php echo $entity['id']; ?>">
<span id="short-description"
    data-editentity="project"
    data-editproperty="short_desc"
    data-editid="<?php echo $entity['id']; ?>">
    <?php echo htmlspecialchars($entity['short_description']); ?></span>
(<a href="#" class="autoedit-control no-visit" data-edittarget="short-description"></a>)
<table class="stripe">
<thead>
    <tr><th>Full Description (<a href="#" class="autoedit-control" data-edittarget="description-text"></a>)</th>
        <th>Maintained by</th>
        <th>Repository (<a href="#" class="autoedit-control" data-edittarget="repository"></a>)</th></tr>
</thead>
<tbody>
    <tr>
        <td id="description-text"
            data-editentity="project"
            data-edittype="textarea"
            data-editproperty="description"
            data-editid="<?php echo $entity['id']; ?>">
            <?php echo htmlspecialchars($entity['description']); ?></td>
        <td><a href="view_entity.php?type=organization&id=<?php echo $entity['owner_id']; ?>">
            <?php echo htmlspecialchars($entity['owner']); ?>
            </a>
        </td>
        <td id="repository"
            data-editentity="project"
            data-editproperty="repo"
            data-editid="<?php echo $entity['id']; ?>">
            <?php echo htmlspecialchars($entity['repo']); ?></td>
    </tr>
</tbody>
</table>
</div>

<div class="quick-view-container">
<h3>Licenses</h3>
<table class="stripe" data-subentity="licenses">
<thead>
    <tr><th data-sourcecol='name'>License</th>
        <th data-sourcecol='descr'>Description</th>
    </tr>
</thead>
<tbody>
    <!-- Filled by BNTLib -->
</tbody>
</thead>
</table>
</div>

<div class="quick-view-container">
<h3>Download Mirrors</h3>
<table class="stripe" data-subentity="dl_mirrors">
<thead>
    <tr><th data-sourcecol='name'>Mirror</th>
        <th data-sourcecol='descr'>Description</th>
    </tr>
</thead>
<tbody>
    <!-- Filled by BNTLib -->
</tbody>
</thead>
</table>
</div>

<div class="quick-view-container">
<h3>Web Sites</h3>
<table class="stripe" data-subentity="websites">
<thead>
    <tr><th data-sourcecol='name'>Site</th>
        <th data-sourcecol='descr'>Description</th>
    </tr>
</thead>
<tbody>
    <!-- Filled by BNTLib -->
</tbody>
</thead>
</table>
</div>

<div class="quick-view-container">
<h3>Contributors</h3>
<table class="stripe" data-subentity="contributors">
<thead>
    <tr><th data-sourcecol='name'>Name</th>
        <th data-sourcecol='email'>Email</th>
        <th data-sourcecol='role'>Role</th>
    </tr>
</thead>
<tbody>
    <!-- Filled by BNTLib -->
</tbody>
</thead>
</table>
</div>