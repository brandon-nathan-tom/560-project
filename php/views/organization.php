<div id="organization-main-info" data-entityid="<?php echo $entity['id']; ?>">
<span id="short-description"
	data-editentity="organization"
	data-editproperty="short_desc"
	data-editid="<?php echo $entity['id']; ?>">
	<?php echo htmlspecialchars($entity['short_description']); ?></span>
(<a href="#" class="autoedit-control no-visit" data-edittarget="short-description"></a>)
<table class="stripe">
<thead>
    <tr><th>Full Description (<a href="#" class="autoedit-control" data-edittarget="description-text"></a>)</th>
		<th>Home Page (<a href="#" class="autoedit-control" data-edittarget="homepage"></a>)</th></tr>
</thead>
<tbody>
    <tr>
        <td id="description-text"
			data-editentity="organization"
			data-edittype="textarea"
			data-editproperty="description"
			data-editid="<?php echo $entity['id']; ?>">
			<?php echo htmlspecialchars($entity['description']); ?></td>
        <td id="homepage"
			data-editentity="organization"
			data-editproperty="homepage"
			data-editid="<?php echo $entity['id']; ?>">
			<a href="<?php echo htmlspecialchars($entity['homepage']); ?>">
            <?php echo htmlspecialchars($entity['homepage']); ?>
            </a>
        </td>
    </tr>
</tbody>
</table>
</div>

<div class="quick-view-container">
<h3>Projects</h3>
<table class="stripe" data-subentity="projects">
<thead>
    <tr><th data-sourcecol='name'>Project</th>
        <th data-sourcecol='repo'>Repository</th>
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
<h3>Members</h3>
<table class="stripe" data-subentity="members">
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