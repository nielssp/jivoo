<?php
$this->extend('generator/layout.html');
?>

<?php echo $Form->formFor($configForm); ?>

<div class="field">
<?php echo $Form->label('name'); ?>
<?php echo $Form->text('name'); ?>
<?php echo $Form->error('name'); ?>
</div>

<div class="field">
<?php echo $Form->label('version'); ?>
<?php echo $Form->text('version'); ?>
<?php echo $Form->error('version'); ?>
</div>

<table>
<thead>
<tr>
<th class="selection"></th>
<th class="primary">Module</th>
</tr>
</thead>
<tbody>
<?php foreach ($availableModules as $module): ?>
<tr>
<td class="selection">
<label>
<?php echo $Form->checkbox('selection', $module); ?>
</label>
</td>
<td class="primary"><?php echo $module; ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php echo $Form->submit(tr('Save'), array('name' => 'save', 'class' => 'primary')); ?>

<?php echo $Form->end(); ?>