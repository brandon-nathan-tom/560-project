<div id="contributor-main-info" data-entityid="<?php echo $entity['id']; ?>">
<table class="stripe">
<thead>
    <tr><th>Name (<a href="#" class="autoedit-control" data-edittarget="contributor-name"></a>)</th>
		<th>Email (<a href="#" class="autoedit-control" data-edittarget="contributor-email"></a>)</th></tr>
</thead>
<tbody>
    <tr>
        <td id="contributor-name"
			data-editentity="contributor"
			data-editproperty="name"
			data-editid="<?php echo $entity['id']; ?>">
			<?php echo htmlspecialchars($entity['name']); ?>
		</td>
		<td id="contributor-email"
			data-editentity="contributor"
			data-editproperty="email"
			data-editid="<?php echo $entity['id']; ?>">
			<a href="mailto:<?php echo htmlspecialchars($entity['email']); ?>">
            <?php echo htmlspecialchars($entity['email']); ?></a>
		</td>
    </tr>
</tbody>
</table>
</div>

<div class="quick-view-container">
<h3>Member Of</h3>
<table class="stripe" data-subentity="organizations">
<thead>
    <tr><th data-sourcecol='name'>Organization</th>
        <th data-sourcecol='short_description'>Description</th>
		<th data-sourcecol='role'>Role</th>
    </tr>
</thead>
<tbody>
    <!-- Filled by BNTLib -->
</tbody>
</thead>
</table>
</div>

<div class="quick-view-container">
<h3>Contributes To</h3>
<table class="stripe" data-subentity="projects">
<thead>
    <tr><th data-sourcecol='name'>Project</th>
        <th data-sourcecol='short_description'>Description</th>
		<th data-sourcecol='role'>Role</th>
    </tr>
</thead>
<tbody>
    <!-- Filled by BNTLib -->
</tbody>
</thead>
</table>
</div>