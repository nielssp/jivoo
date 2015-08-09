<p>
<?php echo tr('Choose a name for your application and select the modules that you want to use.')?>
</p>

<?php $Form->formFor($configForm, null); ?>

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

<div class="field">
<label>Application logic</label>
<?php echo $Form->checkboxAndLabel('modules', 'Controllers'); ?>
<?php echo $Form->checkboxAndLabel('modules', 'Snippets'); ?>
</div>

<div class="field">
<label>Database access</label>
<?php echo $Form->checkboxAndLabel('modules', 'Databases'); ?>
<?php echo $Form->checkboxAndLabel('modules', 'ActiveModels'); ?>
<?php echo $Form->checkboxAndLabel('modules', 'Migrations'); ?>
</div>

<div class="field">
<label>Extensions</label>
<?php echo $Form->checkboxAndLabel('modules', 'Extensions'); ?>
<?php echo $Form->checkboxAndLabel('modules', 'Themes'); ?>
</div>

<div class="field">
<label>Application toolkits</label>
<?php echo $Form->checkboxAndLabel('modules', 'AccessControl'); ?>
<?php echo $Form->checkboxAndLabel('modules', 'Jtk'); ?>
<?php echo $Form->checkboxAndLabel('modules', 'Content'); ?>
<?php echo $Form->checkboxAndLabel('modules', 'Setup'); ?>
</div>

<div class="field">
<label>Development</label>
<?php echo $Form->checkboxAndLabel('modules', 'Console'); ?>
</div>

<?php $this->data->form = $Form->end(); ?>