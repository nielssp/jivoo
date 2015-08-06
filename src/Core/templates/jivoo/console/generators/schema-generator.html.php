<?php $this->import('jquery.js', 'jivoo/console/schema.js'); ?>

<?php echo $Form->form(); ?>

<div class="field">
<?php echo $Form->label('name', tr('Name')); ?>
<?php echo $Form->text('name'); ?>
<div class="help">
<?php echo tr('Name of schema in singular CamelCase, e.g. "Post" or "Comment".')?>
</div>
</div>

<div class="field">
<?php echo $Form->label('features', tr('Features')); ?>
<ul class="checkbox-list">
<li>
<?php echo $Form->checkboxAndLabel('features', 'id', tr('Auto incrementing primary key "id"')); ?>
</li>
<li>
<?php echo $Form->checkboxAndLabel('features', 'timestamps', tr('Timestamps "updated" and "created"')); ?>
</li>
</ul>
</div>

<div class="field">
<?php echo $Form->label('model', tr('Model')); ?>
<?php echo $Form->checkboxAndLabel('model', 'create', tr('Create active model with same name')); ?>
</div>

<div class="block">
<div class="block-header"><h2><?php echo tr('Fields'); ?></h2></div>
<div class="block-content">
<table class="table-va-middle">
<thead>
<th><?php echo tr('Name'); ?></th>
<th><?php echo tr('Type'); ?></th>
<th class="center" style="width:50px"><?php echo tr('Null'); ?></th>
<th><?php echo tr('Default'); ?></th>
<th class="center" style="width:20px"><?php echo $Icon->icon('close'); ?></th>
</thead>
<tbody id="schema-fields">
<tr>
<td><?php echo $Form->text('fields[][name]'); ?></td>
<td>
<?php echo $Form->selectOf('fields[][type]', $types); ?>
</td>
<td class="center"><?php echo $Form->checkbox('fields[][null]', 'null'); ?></td>
<td><?php echo $Form->text('fields[][default]'); ?></td>
<td class="center"><?php echo $Jtk->iconButton(tr('Remove'), 'icon=close size=xs data-remove'); ?></td>
</tr>
</tbody>
</table>
</div>
<div class="block-footer">
<input type="button" id="add-field" value="<?php echo tr('Add field'); ?>" />
</div>
</div>

<div class="buttons">
<?php echo $Form->submit(tr('Create schema')); ?>
<?php echo $Jtk->button(tr('Cancel'), array('route' => 'snippet:Jivoo\Console\Generators'));?>
</div>

<?php echo $Form->end(); ?>