<div class="block-container">

<h2><?php echo tr('Data and models'); ?></h2>
<div class="grid-md grid-1-1">
<div class="cell">

<div class="block">
<div class="block-header">
<h3><?php echo tr('Schemas'); ?></h3>
</div>
<div class="block-content">
<table>
<thead>
<tr>
<th>
<?php echo tr('Schema'); ?>
</th>
<th>
<?php echo tr('Actions'); ?>
</th>
</tr>
</thead>
<tbody>
<?php foreach ($schemas as $schema): ?>
<tr>
<td><?php echo h($schema->getName()); ?></td>
<td>
<div class="button-group">
<?php echo $Html->link('Alter', null, 'class="button button-xs"'); ?>
<?php echo $Html->link('Drop', null, 'class="button button-xs"'); ?>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

</div>
<div class="cell">

<div class="block">
<div class="block-header">
<h3><?php echo tr('Models'); ?></h3>
</div>
<div class="block-content">
<table>
<thead>
<tr>
<th>
<?php echo tr('Model'); ?>
</th>
<th>
<?php echo tr('Actions'); ?>
</th>
</tr>
</thead>
<tbody>
<?php foreach ($models as $model): ?>
<tr>
<td><?php echo h($model); ?></td>
<td>
<div class="button-group">
<?php echo $Html->link('Associations', null, 'class="button button-xs"'); ?>
<?php echo $Html->link('Delete', null, 'class="button button-xs"'); ?>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

</div>
</div>


<h2><?php echo tr('Application logic'); ?></h2>
<div class="grid-md grid-1-1">
<div class="cell">

<div class="block">
<div class="block-header">
<h3><?php echo tr('Controllers'); ?></h3>
</div>
<div class="block-content">
<table>
<thead>
<tr>
<th>
<?php echo tr('Controller'); ?>
</th>
<th>
<?php echo tr('Actions'); ?>
</th>
</tr>
</thead>
<tbody>
<?php foreach ($controllers as $controller): ?>
<tr>
<td><?php echo h($controller); ?></td>
<td>
<div class="button-group">
<?php echo $Html->link('Actions', null, 'class="button button-xs"'); ?>
<?php echo $Html->link('Delete', null, 'class="button button-xs"'); ?>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

</div>
<div class="cell">

<div class="block">
<div class="block-header">
<h3><?php echo tr('Snippets'); ?></h3>
</div>
<div class="block-content">
<table>
<thead>
<tr>
<th>
<?php echo tr('Snippet'); ?>
</th>
<th>
<?php echo tr('Actions'); ?>
</th>
</tr>
</thead>
<tbody>
<?php foreach ($snippets as $snippet): ?>
<tr>
<td><?php echo h($snippet); ?></td>
<td>
<div class="button-group">
<?php echo $Html->link('Delete', null, 'class="button button-xs"'); ?>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

</div>
</div>

</div>