<?php $this->layout('jivoo/setup/layout.html'); ?>


<?php $Form->form(null); ?>


<?php if (!isset($existing)): ?>
<p>
<?php echo tr('An existing installation has been found.'); ?>
 <?php echo tr('Would you like to use the existing data and upgrade tables if necessary?'); ?>
</p>

<p>
<?php echo $Form->submit(tr('Migrate existing tables'), 'name=migrate'); ?>
</p>

<p>
<?php echo tr('Or, would you like to delete all existing tables?'); ?>
</p>

<?php else: ?>

<p>
<?php echo tr('The following tables already exist in the database: %1{, }{, and }.', $existing)?>
 <?php echo tr('Would you like to delete them and do a clean install?'); ?>
</p>
<?php endif; ?>

<p>
<?php echo $Form->submit(tr('Clean install'), 'name=clean'); ?>
</p>

<?php $this->data->form = $Form->end(); ?>