<div class="jivoo-system-info">
<div class="jivoo-system-info-header">
<?php echo tr('Instance'); ?>
</div>
<div class="jivoo-system-info-entry">
<strong><?php echo tr('Instance files'); ?></strong>
<?php echo h($userDir); ?>
</div>
<div class="jivoo-system-info-entry">
<strong><?php echo tr('Entry script'); ?></strong>
<?php echo h($entryScript); ?>
</div>
<div class="jivoo-system-info-entry">
<strong><?php echo tr('Environment'); ?></strong>
<?php echo h($environment); ?>
</div>
</div>

<div class="jivoo-system-info">
<div class="jivoo-system-info-header">
<?php echo tr('Application'); ?>
</div>
<div class="jivoo-system-info-entry">
<strong><?php echo tr('Path'); ?></strong>
<?php echo h($appDir); ?>
</div>
<div class="jivoo-system-info-entry">
<strong><?php echo tr('Name'); ?></strong>
<?php echo h($app['name']); ?>
</div>
<div class="jivoo-system-info-entry">
<strong><?php echo tr('Version'); ?></strong>
<?php echo h($app['version']); ?>
</div>
<div class="jivoo-system-info-entry">
<strong><?php echo tr('Shared application files'); ?></strong>
<?php echo h($shareDir); ?>
</div>
</div>

<div class="jivoo-system-info">
<div class="jivoo-system-info-header">
<?php echo tr('Library'); ?>
</div>

<div class="jivoo-system-info-entry">
<strong><?php echo tr('Path'); ?></strong>
<?php echo h(Jivoo\PATH); ?>
</div>
<div class="jivoo-system-info-entry">
<strong><?php echo tr('%1 version', 'Jivoo'); ?></strong>
<?php echo h(Jivoo\VERSION); ?>
</div>
</div>

<div class="jivoo-system-info">
<div class="jivoo-system-info-header">
<?php echo tr('System'); ?>
</div>

<div class="jivoo-system-info-entry">
<strong><?php echo tr('Operating system'); ?></strong>
<?php echo h(php_uname()); ?>
</div>
<div class="jivoo-system-info-entry">
<strong><?php echo tr('PHP version'); ?></strong>
<?php echo h(phpversion()); ?>
</div>
<div class="jivoo-system-info-entry">
<strong><?php echo tr('Server API'); ?></strong>
<?php echo h(php_sapi_name()); ?>
</div>

</div>