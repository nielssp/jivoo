<?php
$this->extend('setup/layout.html');
?>

<?php echo $Form->formFor($setupForm); ?>

      <div class="section">
        <div class="container">
          <h1><?php echo tr('Welcome to %1', $app['name']); ?></h1>
          <p>
          <?php echo tr('You have selected the %1 database driver.',
            '<strong>' . $driver['name'] . '</strong>'); ?>
          	<?php echo tr('The following information is required.'); ?>
          </p>
          <?php if (isset($exception)) : ?>
          <p class="error">
            <?php echo tr('An error occured:'); ?>
            <?php echo $exception->getMessage(); ?>
          </p>
          <?php endif; ?>
          <?php foreach ($setupForm->getModel()->getFields() as $field) : ?>
          <p>
            <?php echo $Form->label($field, null, array('class' => 'small')); ?>
            <?php echo $Form->text($field, array('class' => 'text')); ?>
              <?php if ($Form->isValid($field)) : ?>
            <span class="description">
              <?php echo $Form->ifOptional($field, tr('Optional.')); ?> 
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
            <span class="description error">
              <?php echo $Form->error($field); ?>
              <?php endif; ?>
            </span>
          </p>
          <?php endforeach; ?>

      <div class="section">
        <div class="container">
          <div class="aright">
            <?php echo $Form->submit(tr('Cancel'), array('name' => 'cancel', 'class' => 'button')); ?>
            <?php echo $Form->submit(tr('Save'), array('name' => 'save', 'class' => 'button publish')); ?>
          </div>
        </div>
      </div>
<?php echo $Form->end(); ?>


