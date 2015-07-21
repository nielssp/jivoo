
<?php echo $Form->form(); ?>

<div class="field">
<?php echo $Form->label('name', tr('Name')); ?>
<?php echo $Form->text('name'); ?>
<?php echo tr('Name of schema in singular CamelCase, e.g. "Post" or "Comment".')?>
</div>

<div class="field">
<?php echo $Form->label('features', tr('Features')); ?>
<div>
<?php echo $Form->checkboxAndLabel('features', 'id', tr('Auto incrementing primary key "id"')); ?>
</div>
<div>
<?php echo $Form->checkboxAndLabel('features', 'timestamps', tr('Timestamps "updated" and "created"')); ?>
</div>
</div>

<div class="field">
<?php echo $Form->label('model', tr('Model')); ?>
<div>
<?php echo $Form->checkboxAndLabel('model', 'create', tr('Create active model with same name')); ?>
</div>
</div>

<div class="block">
<div class="block-header"><h2><?php echo tr('Fields'); ?></h2></div>
<div class="block-content">
<table>
<thead>
<th><?php echo tr('Name'); ?></th>
<th><?php echo tr('Type'); ?></th>
<th><?php echo tr('Null'); ?></th>
<th><?php echo tr('Default'); ?></th>
</thead>
<tbody>
<tr>
<td><?php echo $Form->text('fields[][name]'); ?></td>
<td><?php echo $Form->text('fields[][type]'); ?></td>
<td><?php echo $Form->checkbox('fields[][null]', 'null'); ?></td>
<td><?php echo $Form->text('fields[][default]'); ?></td>
</tr>
</tbody>
</table>
</div>
<div class="block-footer">
<input type="button" class="add" value="<?php echo tr('Add field'); ?>" />
</div>
</div>

<?php echo $Form->submit(tr('Create')); ?>

<?php echo $Form->end(); ?>