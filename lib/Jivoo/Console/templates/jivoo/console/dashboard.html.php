<p><?php echo tr('Welcome to the Jivoo web application framework.')?></p>

<h2><?php echo tr('Application'); ?></h2>

<table class="trace">
<tbody>
<tr>
<td><?php echo tr('%1 version', $app['name']); ?></td>
<td><?php echo $app['version']; ?></td>
</tr>
</tbody>
</table>

<h2><?php echo tr('System'); ?></h2>

<table class="trace">
<tbody>
<tr>
<td><?php echo tr('Operating system'); ?></td>
<td><?php echo php_uname(); ?></td>
</tr>
<tr>
<td><?php echo tr('PHP version'); ?></td>
<td><?php echo phpversion(); ?></td>
</tr>
<tr>
<td><?php echo tr('Server API'); ?></td>
<td><?php echo php_sapi_name(); ?></td>
</tr>
<tr>
<td><?php echo tr('%1 version', 'Jivoo'); ?></td>
<td><?php echo Jivoo\Core\VERSION; ?></td>
</tbody>
</table>