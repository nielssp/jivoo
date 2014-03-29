<?php
$this->extend('setup/layout.html');
?>

      <?php echo $Form->form(); ?>

      <div class="section">
        <div class="container">
          <h1><?php echo tr('Unable to save configuration'); ?></h1>
          <p><?php echo tr('%1 could not save the configuration to the following file:', $app['name']); ?></p>
          <p><code><?php echo $file; ?></code></p>
          <?php if ($exists): ?>
          <p><?php echo tr('The file exists, but %1 does not have permission to write to it.', $app['name']); ?></p>
          <p><?php echo tr('You should change the access permission or ownership of the file to allow %1 to write to it. ', $app['name']); ?></p>
          <p><?php echo $Html->link(tr('Click here to get more help.', 'http://apakoh.dk')); ?></p>
          <p><?php echo tr('Alternatively, you can manually edit the file.')?></p>
          <?php else: ?>
          <p><?php echo tr('The file does not exists, and %1 does not have permission to create it.', $app['name']); ?>
          <p><?php echo tr('Please create the file with the permissions necessary for %1 to write to it, then refresh this page.', $app['name']); ?>
          <?php endif; ?>
          <h2><?php echo tr('Unsaved data'); ?></h2>
          <textarea style="height:100px;"><?php echo h($data); ?></textarea>
        </div>
      </div>
      
      <div class="section">
        <div class="container">
          <div class="aright">
            <?php echo $Form->submit(tr('Cancel'), 
    array('class' => 'button publish')); ?>
          </div>
        </div>
      </div>

      <?php echo $Form->end(); ?>
