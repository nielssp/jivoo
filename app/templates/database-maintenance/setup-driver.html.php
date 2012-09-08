<?php
/*
 * Template for "404 not found"
 */

// Render the header
$this->render('backend/header.html');
?>

<?php echo $Form->begin($setupForm); ?>

      <div class="section">
        <div class="container">
          <h1>Welcome to PeanutCMS</h1>
          <p>
          	You have selected the <strong><?php echo $driver['name']; ?></strong> database driver.
          	The following information is required.
          </p>
          <?php if (isset($exception)): ?>
          <p class="error">
            <?php echo tr('An error occured:'); ?>
            <?php echo $exception->getMessage(); ?>
          </p>
          <?php endif; ?>
          <?php foreach ($setupForm->getFields() as $field): ?>
          <p>
            <?php echo $Form->label($field, null, array('class' => 'small')); ?>
            <?php echo $Form->field($field); ?>
              <?php if ($Form->isValid($field)): ?>
            <span class="description">
              <?php echo $Form->isOptional($field, tr('Optional.')); ?> 
<?php
switch ($field) {
  case 'filename':
    echo tr('The location of the database.');
    break;
  case 'tablePrefix':
    echo tr('Can be used to prevent conflict with other tables in the database.');
    break;
}
?>
              <?php else: ?>
            <span class="description error">
              <?php echo $Form->getError($field); ?>
              <?php endif; ?>
            </span>
          </p>
          <?php endforeach; ?>

      <div class="section">
        <div class="container">
          <div class="aright">
            <?php echo $Form->submit(tr('Cancel'), 'cancel'); ?>
            <?php echo $Form->submit(tr('Save'), 'save', array('class' => 'button publish')); ?>
          </div>
        </div>
      </div>

<?php echo $Form->end(); ?>

<?php
$this->render('backend/footer.html');
?>

