<?php
$this->extend('jivoo/generators/layout.html');
?>

<p>
<?php echo tr('Choose a name for you application and select the modules that you want to use.')?>
</p>

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

<h2>Modules</h2>

<div class="field">
<label>Base</label>
Assets, Helpers, Models, Routing, View
</div>

<?php 
$moduleCheckbox = function($module, $checked = false) use($Form) {
  return $Form->checkbox('modules', $module, $checked ? array('checked' => 'checked') : array())
    . $Form->checkboxLabel('modules', $module, $module);
}
?>

<div class="field">
<label>Application logic</label>
<?php echo $moduleCheckbox('Controllers'); ?>
<?php echo $moduleCheckbox('Snippets'); ?>
</div>

<div class="field">
<label>Database access</label>
<?php echo $moduleCheckbox('Databases'); ?>
<?php echo $moduleCheckbox('ActiveModels'); ?>
<?php echo $moduleCheckbox('Migrations'); ?>
</div>

<div class="field">
<label>Extensions</label>
<?php echo $moduleCheckbox('Extensions'); ?>
<?php echo $moduleCheckbox('Themes'); ?>
</div>

<div class="field">
<label>Application toolkits</label>
<?php echo $moduleCheckbox('AccessControl'); ?>
<?php echo $moduleCheckbox('Administration'); ?>
<?php echo $moduleCheckbox('Content'); ?>
<?php echo $moduleCheckbox('Setup'); ?>
</div>

<div class="field">
<label>Development</label>
<?php echo $moduleCheckbox('Console'); ?>
<?php echo $moduleCheckbox('Generators'); ?>
</div>

<?php echo $Form->submit(tr('Save'), array('name' => 'save', 'class' => 'primary')); ?>

<?php echo $Form->end(); ?>