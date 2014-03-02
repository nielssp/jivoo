<?php
$this->extend('setup/layout.html');
?>

<?php echo $Form->formFor($user); ?>

      <div class="section">
        <div class="container">
          <h1><?php echo tr('Welcome to %1', $app['name']); ?></h1>
          <p>
            <?php echo tr('Please select a username and a password.'); ?>
          </p>
          <p>
            <?php echo $Form->label('username', null, array('class' => 'small')); ?>
            <?php echo $Form->text('username', array('class' => 'text')); ?>
              <?php if ($Form->isValid('username')) : ?>
            <span class="description">
              <?php echo $Form->ifOptional('username', tr('Optional.')); ?> 
              <?php 
else : ?>
            <span class="description error">
              <?php echo $Form->getError('username'); ?>
              <?php endif; ?>
            </span>
          </p>

          <p>
            <?php echo $Form->label('password', null, array('class' => 'small')); ?>
            <?php echo $Form->password('password', array('class' => 'text')); ?>
              <?php if ($Form->isValid('password')) : ?>
            <span class="description">
              <?php echo $Form->ifOptional('password', tr('Optional.')); ?> 
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
    <?php echo $Form->password('confirm_password', array('class' => 'text')); ?>
              <?php if ($Form->isValid('confirm_password')) : ?>
            <span class="description">
              <?php echo $Form->ifOptional('confirm_password', tr('Optional.')); ?> 
              <?php 
else : ?>
            <span class="description error">
              <?php echo $Form->getError('confirm_password'); ?>
              <?php endif; ?>
            </span>
          </p>

          <p>
            <?php echo $Form->label('email', null, array('class' => 'small')); ?>
            <?php echo $Form->text('email', array('type' => 'email', 'class' => 'text')); ?>
              <?php if ($Form->isValid('email')) : ?>
            <span class="description">
              <?php echo $Form->ifOptional('email', tr('Optional.')); ?> 
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
            <?php echo $Form->submit(tr('Skip'), array('class' => 'button')); ?>
            <?php echo $Form->submit(tr('Save'), array('class' => 'button publish')); ?>
          </div>
        </div>
      </div>
<?php echo $Form->end(); ?>


