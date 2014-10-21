<?php $this->extend('admin/layout.html'); ?>

<?php if (!$record and !$selection): ?>

<div class="flash flash-warn"><?php echo tr('The record does not exist.'); ?></div>

<?php else: ?>

<?php echo $Form->form(); ?>

<p><?php echo $confirmation; ?></p>

<?php echo $Form->submit(tr('OK')); ?>

<?php echo $Form->submit(tr('Cancel'), array('name' => 'cancel')); ?>

<?php echo $Form->end(); ?>

<?php endif; ?>