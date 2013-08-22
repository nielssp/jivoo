<?php
// Render the header
$this->render('setup/header.html');
?>

<?php echo $Form->begin($user); ?>

      <div class="section">
        <div class="container">
          <h1><?php echo tr('Welcome to %1', $app['name']); ?></h1>
          <p>
            <?php echo tr('Please select a username and a password.'); ?>
          </p>
          <p>
            <?php echo $Form->label('username', null, array('class' => 'small')); ?>
            <?php echo $Form->field('username'); ?>
              <?php if ($Form->isValid('username')) : ?>
            <span class="description">
              <?php echo $Form->isOptional('username', tr('Optional.')); ?> 
              <?php 
else : ?>
            <span class="description error">
              <?php echo $Form->getError('username'); ?>
              <?php endif; ?>
            </span>
          </p>

          <p>
            <?php echo $Form->label('password', null, array('class' => 'small')); ?>
            <?php echo $Form->field('password'); ?>
              <?php if ($Form->isValid('password')) : ?>
            <span class="description">
              <?php echo $Form->isOptional('password', tr('Optional.')); ?> 
              <?php 
else : ?>
            <span class="description error">
              <?php echo $Form->getError('password'); ?>
              <?php endif; ?>
            </span>
          </p>

          <p>
            <?php echo $Form->label('confirm_password', null,
    array('class' => 'small')); ?>
            <?php echo $Form->field('confirm_password'); ?>
              <?php if ($Form->isValid('confirm_password')) : ?>
            <span class="description">
              <?php echo $Form->isOptional('confirm_password', tr('Optional.')); ?> 
              <?php 
else : ?>
            <span class="description error">
              <?php echo $Form->getError('confirm_password'); ?>
              <?php endif; ?>
            </span>
          </p>

          <p>
            <?php echo $Form->label('email', null, array('class' => 'small')); ?>
            <?php echo $Form->field('email'); ?>
              <?php if ($Form->isValid('email')) : ?>
            <span class="description">
              <?php echo $Form->isOptional('email', tr('Optional.')); ?> 
              <?php 
else : ?>
            <span class="description error">
              <?php echo $Form->getError('email'); ?>
              <?php endif; ?>
            </span>
          </p>

      <div class="section">
        <div class="container">
          <div class="aright">
            <?php echo $Form->submit(tr('Skip'), 'skip'); ?>
            <?php echo $Form->submit(tr('Save'), 'save',
    array('class' => 'button publish')); ?>
          </div>
        </div>
      </div>
<?php echo $Form->end(); ?>

<?php
$this->render('setup/footer.html');
?>

