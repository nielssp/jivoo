<?php $this->extend('admin/layout.html'); ?>

<table>
<tbody>
<tr>
<td>Operating system</td>
<td><?php echo php_uname(); ?></td>
</tr>
<tr>
<td>PHP version</td>
<td><?php echo phpversion(); ?></td>
</tr>
<tr>
<td><?php echo $app['name']; ?> version</td>
<td><?php echo $app['version']; ?></td>
</tr>
</tbody>
</table>
