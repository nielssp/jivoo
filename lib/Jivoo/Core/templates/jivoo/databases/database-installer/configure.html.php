<?php $this->layout('jivoo/setup/layout.html'); ?>

<?php echo $Form->formFor($driverForm, null); ?>

<p><?php echo tr(
  'You have selected the %1 database driver.',
  '<strong>' . $driver['name'] . '</strong>');
?>
 <?php echo tr('The following information is required.'); ?>
</p>

<?php foreach ($driverForm->getFields() as $field) : ?>
<?php
switch ($field) {
  case 'filename':
    $description = tr('The location of the database.');
    break;
  case 'tablePrefix':
    $description = tr(
      'Can be used to prevent conflict with other tables in the database.');
    break;
  default:
    $description = null;
}
?>
<?php echo $Form->field($field, array('description' => $description)); ?>
<?php endforeach; ?>

<?php $this->data->form = $Form->end(); ?>