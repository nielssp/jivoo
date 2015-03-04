<?php $this->layout('setup/layout.html'); ?>

<?php echo $Form->formFor($setupForm); ?>

<p><?php echo tr(
  'You have selected the %1 database driver.',
  '<strong>' . $driver['name'] . '</strong>');
?>
<?php echo tr('The following information is required.'); ?>
</p>
<?php if (isset($exception)) : ?>
<div class="flash flash-error">
<?php echo tr('An error occured:'); ?> 
<?php echo $exception->getMessage(); ?>
</div>
<?php endif; ?>
<?php foreach ($setupForm->getModel()->getFields() as $field) : ?>
<div class="field<?php echo $Form->ifRequired($field, ' field-required'); ?>">
<?php echo $Form->label($field); ?>
<?php echo $Form->text($field); ?>
<?php if ($Form->isValid($field)) : ?> 
<?php
switch ($field) {
  case 'filename':
    echo tr('The location of the database.');
    break;
  case 'tablePrefix':
    echo tr(
      'Can be used to prevent conflict with other tables in the database.');
    break;
}
?>
<?php else : ?>
<?php echo $Form->error($field); ?>
<?php endif; ?>
</div>
<?php endforeach; ?>

<?php echo $Form->submit(tr('Save'), array('name' => 'save', 'class' => 'primary')); ?>
<?php echo $Form->submit(tr('Cancel'), array('name' => 'cancel')); ?>

<?php echo $Form->end(); ?>


