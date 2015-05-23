<p><?php echo tr('Welcome to the Jivoo web application framework.')?></p>

<h2><?php echo tr('Instance'); ?></h2>

<table class="trace">
<tbody>
<tr>
<td><?php echo tr('Instance files'); ?></td>
<td><?php echo h($userDir); ?></td>
</tr>
<tr>
<td><?php echo tr('Entry script'); ?></td>
<td><?php echo h($entryScript); ?></td>
</tr>
<tr>
<td><?php echo tr('Environment'); ?></td>
<td><?php echo h($environment); ?></td>
</tr>
</tbody>
</table>

<h2><?php echo tr('Application'); ?></h2>

<table class="trace">
<tbody>
<tr>
<td><?php echo tr('Path'); ?></td>
<td><?php echo h($appDir); ?></td>
</tr>
<tr>
<td><?php echo tr('Name'); ?></td>
<td><?php echo h($app['name']); ?></td>
</tr>
<tr>
<td><?php echo tr('Version'); ?></td>
<td><?php echo h($app['version']); ?></td>
</tr>
<tr>
<td><?php echo tr('Shared application files'); ?></td>
<td><?php echo h($shareDir); ?></td>
</tr>
</tbody>
</table>

<h2><?php echo tr('Library'); ?></h2>

<table class="trace">
<tbody>
<tr>
<td><?php echo tr('Path'); ?></td>
<td><?php echo h(Jivoo\PATH); ?></td>
</tr>
<tr>
<td><?php echo tr('%1 version', 'Jivoo'); ?></td>
<td><?php echo h(Jivoo\Core\VERSION); ?></td>
</tr>
</tbody>
</table>

<h2><?php echo tr('System'); ?></h2>

<table class="trace">
<tbody>
<tr>
<td><?php echo tr('Operating system'); ?></td>
<td><?php echo h(php_uname()); ?></td>
</tr>
<tr>
<td><?php echo tr('PHP version'); ?></td>
<td><?php echo h(phpversion()); ?></td>
</tr>
<tr>
<td><?php echo tr('Server API'); ?></td>
<td><?php echo h(php_sapi_name()); ?></td>
</tr>
</tbody>
</table>