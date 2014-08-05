<?php $this->extend('admin/layout.html'); ?>

<p>Welcome to the Jivoo App Development Kit.</p>

<h2>Applications (<?php echo $appDir; ?>)</h2>

<table>
<thead>
<tr>
<th>Application</th>
<th>Version</th>
<th>Location</th>
</tr>
</thead>
<tbody>
<?php foreach ($apps as $name => $app): ?>
<tr>
<td><?php echo $Html->link($app['name'], array(
  'controller' => 'Applications',
  'action' => 'dashboard',
  $name
)); ?></td>
<td><?php echo $app['version']; ?></td>
<td><?php echo $app['path']; ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<p>
<?php echo $Icon->link(tr('Create application'), 'Applications::create', 'wand', null, array('class' => 'button')); ?>

<?php echo $Icon->link(tr('Add application'), 'Applications::add', 'box-add', null, array('class' => 'button')); ?>
</p>

<h2>Libraries (<?php echo LIB_PATH; ?>)</h2>

<table>
<thead>
<tr>
<th>Module</th>
<th>Version</th>
</tr>
</thead>
<tbody>
<?php foreach ($libs as $lib): ?>
<tr>
<td><?php echo $lib; ?></td>
<td>Unknown</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<h2>System</h2>

<table>
<tbody>
<tr>
<td>Operating system</td>
<td><?php echo php_uname(); ?></td>
</tr>
<tr>
<td>PHP version</td>
<td><?php echo phpversion(); ?></td>
</tr>
<tr>
<td>Document root</td>
<td><?php echo $_SERVER['DOCUMENT_ROOT']; ?></td>
</tr>
</tbody>
</table>
