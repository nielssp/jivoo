<?php $this->layout('jivoo/setup/layout.html'); ?>

<?php $Form->form(null); ?>

<p><?php echo tr('Please select your desired database driver from the list below.'); ?></p>

<table>
<tbody>
<?php
foreach ($drivers as $driver) :
?>
<tr>
<td><?php echo $driver['name']; ?></td>
<?php if ($driver['isAvailable']) : ?>
<td>
<?php echo tr('Available'); ?>
</td>
<td>
<?php echo $Form->submit(
  tr('Select %1', $driver['name']),
  array('name' => $Form->name($driver['driver']), 'class' => 'primary')
); ?>
</td>
<?php else : ?>
<td colspan="2" class="error">
<?php
echo tn(
  'Unavailable. Missing the "%1{", "}{" and "}" PHP extensions',
  'Unavailable. Missing the "%1{", "}{" and "}" PHP extension',
  $driver['missingExtensions']
);
?>
</td>
<?php endif; ?>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php $this->data->form = $Form->end(); ?>